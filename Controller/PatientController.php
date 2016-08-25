<?php

namespace ScheduleBundle\Controller;

use ScheduleBundle\Entity\Individ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ScheduleBundle\Entity\Patient;
use ScheduleBundle\Entity\Population;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Patient controller use Genetic algorithm to calculate patient's schedule
 *
 * @Route("/reception/schedule")
 */
class PatientController extends Controller
{
    private $workTime;

    private $iterationNumber;

    const ITERATIONS_NUMBER = 5;
    const MIN_NUMBER_OF_PATIENTS = 4;
    const MAX_NUMBER_OF_PATIENTS = 18;
    const RANDOM_NUMBER = 5; // mutation probability
    const WORKING_TIME_IN_MINUTES = 240;

    /**
     * PatientController constructor.
     */
    public function __construct()
    {
        $this->iterationNumber = self::ITERATIONS_NUMBER;
    }

    /**
     * @return mixed
     */
    public function getIterationNumber()
    {
        return $this->iterationNumber;
    }

    /**
     * @param mixed $iterationNumber
     */
    public function setIterationNumber($iterationNumber)
    {
        $this->iterationNumber = $iterationNumber;
    }

    /**
     * @return mixed
     */
    public function getWorkTime()
    {
        return $this->workTime;
    }

    /**
     * @param mixed $workTime
     */
    public function setWorkTime($workTime)
    {
        $this->workTime = $workTime;
    }

    /**
     * Lists all Patient entities.
     *
     * @Route("/", name="reception_schedule_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $number = $request->request->get("number");
            if ($number < self::MIN_NUMBER_OF_PATIENTS || $number > self::MAX_NUMBER_OF_PATIENTS) {
                return new JsonResponse("error", 500);
            }
            $i = 0;
            $last = false;
            $form = $this->createFormBuilder()
                ->add('name', EntityType::class, array(
                    'label' => 'Тип візиту',
                    'class' => 'GuideBundle:VisitTypes',
                    'choices_as_values' => true,
                    'choice_label' => 'name',
                ))
                ->getForm();
            while ($i != $number) {
                if ($i == $number - 1) {
                    $last = true;
                }
                $forms[] = $this->renderView('patient/workforms.html.twig', array(
                    "form" => $form->createView(),
                    "num" => $i,
                    "last" => $last
                ));
                $i++;
            }
            return new JsonResponse(array("content" => $forms));
        } else {
            $form = $this->createFormBuilder()
                ->add('number', IntegerType::class, array('label' => 'Кількість пацієнтів',
                    'attr' => array('class' => 'validate[required,max['.self::MAX_NUMBER_OF_PATIENTS.'], min['.self::MAX_NUMBER_OF_PATIENTS.']]')))
                ->add('save', SubmitType::class, array('label' => 'Підтвердити'))
                ->getForm();
            return $this->render('patient/start.html.twig', array(
                'form' => $form->createView()
            ));
        }
    }

    /**
     * Lists all Patient entities.
     *
     * @Route("/submit", name="reception_schedule_submit")
     * @Method("POST")
     */
    public function submitAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $works = $request->request->get('works');
            $patients = array();
            $em = $this->getDoctrine()->getEntityManager();
            //$em->getRepository('ScheduleBundle:Patient');
            $queryBuilder = $em
                ->createQueryBuilder()
                ->delete('ScheduleBundle:Patient', 'a');
            $queryBuilder->getQuery()->execute();
            foreach ($works as $id => $work) {
                $patient = new Patient($id, $work);
                $em->persist($patient);
                $em->flush();
            }
            return new JsonResponse(array("process" => true));
        } else {
            return new JsonResponse("error", 500);
        }
    }

    private function reanimate($children, $selection)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $childs = array();
        foreach ($children as $child) {
            if ($selection) {
                $pats = $child->getPatients()->toArray();
                $newPats = $this->changeGenes($pats);
                $times = $em->getRepository("ScheduleBundle:works")->findAll();
                $ch = $this->createIndivid($newPats, $times, true);
                $childs[] = $ch;
            } else {
                if (!$child->getSuitable()) {
                    $pats = $child->getPatients()->toArray();
                    $newPats = $this->changeGenes($pats);
                    $times = $em->getRepository("ScheduleBundle:works")->findAll();
                    $ch = $this->createIndivid($newPats, $times, true);
                    $childs[] = $ch;
                } else {
                    $childs[] = $child;
                }
            }

        }
        return $childs;
    }

    private function startWar($population)
    {
        $result = new Population();
        if ($population->warNeed()) {
            $enemyPopulation = $this->buildPopulation();
            if ($population->getSumBoysTime() <= $enemyPopulation->getSumBoysTime()) {
                foreach ($population->getIndivids() as $individ) {
                    $result->addIndivid($individ);
                }
                foreach ($enemyPopulation->getIndivids() as $enemy) {
                    if ($enemy->getGender() === 1) {
                        $result->addIndivid($enemy);
                    }
                }
            } else {
                foreach ($enemyPopulation->getIndivids() as $enemy) {
                    $result->addIndivid($enemy);
                }
                foreach ($population->getIndivids() as $individ) {
                    if ($individ->getGender() === 1) {
                        $result->addIndivid($individ);
                    }
                }
            }
            $result->getMaxEvLength();
            $result->getMaxMorLength();
            return array(
                "enemy" => $enemyPopulation,
                "popSum" => $population->getSumBoysTime(),
                "enemySum" => $enemyPopulation->getSumBoysTime(),
                "resultPop" => $result
            );
        } else {
            return false;
        }
    }

    /**
     * @Route("/result", name="reception_schedule_result")
     * @Method({"GET", "POST"})
     */
    public function resultAction()
    {
        $iterations = array();
        $iterate = array();
        $population = $this->buildPopulation();
        $iterPopulation = $population;
        for ($i = 0; $i < $this->getIterationNumber(); $i++) {
            $result = $this->startWar($iterPopulation);
            if ($result) {
                $iterPopulation = $result["resultPop"];
            }
            $parents = $this->getNewParents($iterPopulation);
            $children = $this->getNewChildren($parents);
            if (rand(1, 100) === self::RANDOM_NUMBER) { // mutation probaility
                $mutated = $this->reanimate($children, true);
                $childSelection = $this->reanimate($mutated, true);
                $childReanimate = $this->reanimate($childSelection, false);
            } else {
                $childSelection = $this->reanimate($children, true);
                $childReanimate = $this->reanimate($childSelection, false);
            }
            $newPopulation = $this->getNewPopulation($iterPopulation, $childReanimate);
            $iterPopulation = $newPopulation;
            $iterations[$i][] = array("children" => $children);
            if ($result) {
                $iterations[$i][] = array(
                    "newPopulation" => $newPopulation->getIndivids(),
                    "parents" => $parents,
                    "selection" => $childSelection,
                    "reanimation" => $childReanimate,
                    "mutated" => $mutated,
                    "enemyPopulation" => $result["enemy"]->getIndivids(),
                    "popSum" => $result["popSum"],
                    "enemySum" => $result["enemySum"],
                    "result" => $result["resultPop"]->getIndivids()
                );
            } else {
                $iterations[$i][] = array(
                    "newPopulation" => $newPopulation->getIndivids(),
                    "parents" => $parents,
                    "selection" => $childSelection,
                    "reanimation" => $childReanimate,
                    "mutated" => $mutated
                );
            }

        }
        $winner = $this->getWinner($iterPopulation);
        return $this->render('patient/result.html.twig', array(
            "population" => $population->getIndivids(),
            "maxMorLength" => $population->maxMorLength,
            "maxEvLength" => $population->maxEvLength,
            "maxLength" => $population->maxEvLength + $population->maxMorLength,
            "iterations" => $iterations,
            "iterate" => $iterate,
            "winner" => array($winner)
        ));
    }

    private function getNewPopulation($population, $children)
    {
        $newPopulation = new Population();
        foreach ($population->getIndivids() as $ind) {
            if ($ind->getPatients() !== $population->getMostWeak()->getPatients()) {
                $newPopulation->addIndivid($ind);
            }
        }
        foreach ($children as $child) {
            if ($child->getSuitable() && !in_array($child, $newPopulation->getIndivids())) {
                $newPopulation->addIndivid($child);
            }
        }
        $newPopulation->getMaxMorLength();
        $newPopulation->getMaxEvLength();
        return $newPopulation;
    }

    private function getWinner($population)
    {
        $min = 1000000;
        $winner = new Individ();
        foreach ($population->getIndivids() as $individ) {
            if ($individ->getAverQueueTime() < $min) {
                $min = $individ->getAverQueueTime();
                $winner = $individ;
            }
        }
        return $winner;
    }

    private function getNewParents(Population $population) //1 girls, 0 boys
    {
        $parents = array();
        $girls = array();
        $boys = array();
        foreach ($population->getIndivids() as $individ) {
            if ($individ->getGender() === 1) {
                $girls[] = $individ;
            } else {
                $boys[] = $individ;
            }
        }
        $girlsKey = array_rand($girls, 1);
        $boysKey = array_rand($boys, 1);
        $parents[0] = $girls[$girlsKey];
        $parents[1] = $boys[$boysKey];
        return $parents;
    }

    private function getNewChildren($parents)
    {
        $children = array();
        $children[0] = $this->getChild($parents[0]->getPatients(), $parents[1]->getPatients());
        $children[1] = $this->getChild($parents[1]->getPatients(), $parents[0]->getPatients());
        return $children;
    }

    private function getChild($parentFirst, $parentSecond)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $child = new Individ();
        $childPat = array();
        $i = 0;
        $j = count($parentSecond) - 1;
        while ($i != count($parentFirst) && $j != -1) {
            if (!in_array($parentFirst[$i], $childPat)) {
                $childPat[] = $parentFirst[$i];
                $i++;
            } else {
                $i++;
            }
            if (!in_array($parentSecond[$j], $childPat)) {
                $childPat[] = $parentSecond[$j];
                $j--;
            } else {
                $j--;
            }
        }
        foreach ($childPat as $patient) {
            $child->add($patient);
        }
        $times = $em->getRepository("ScheduleBundle:works")->findAll();
        return $this->createIndivid($childPat, $times, true);
    }

    private function changeGenes($individ, $switch = 'selection')
    {
        $firstChange = array_rand($individ, 1);
        $secondChange = array_rand($individ, 1);
        while ($firstChange === $secondChange) {
            $firstChange = array_rand($individ, 1);
            $secondChange = array_rand($individ, 1);
        }
        $temp = $individ[$firstChange];
        $individ[$firstChange] = $individ[$secondChange];
        $individ[$secondChange] = $temp;
        return $individ;
    }

    private function buildPopulation()
    {
        $continue = true;
        $this->workTime = self::WORKING_TIME_IN_MINUTES;
        $em = $this->getDoctrine()->getEntityManager();
        $scheduleBase = $em->getRepository('ScheduleBundle:Patient')->findAll();
        $em = $this->getDoctrine()->getEntityManager();
        $times = $em->getRepository("ScheduleBundle:works")->findAll();
        $population = new Population();
        $i = 0;
        while ($continue) {
            shuffle($scheduleBase);
            $individ = $this->createIndivid($scheduleBase, $times);
            if (!in_array($individ, $population->getIndivids()) && $individ->getSuitable()) {
                $population->addIndivid($individ);
            }
            if (count($population->getIndivids()) === $population->getSize() || $i > 100) {
                $continue = false;
            }
            $i++;
        }
        $population->getMaxMorLength();
        $population->getMaxEvLength();
        return $population;
    }

    private function isSuitable(Individ $individ)
    {
        if ($individ->getMorTime() > $this->workTime || $individ->getEvTime() > $this->workTime) {
            return false;
        } else {
            return true;
        }
    }

    private function factorial($number)
    {
        if ($number === 1) {
            return 1;
        } else {
            return $number * $this->factorial($number - 1);
        }
    }

    private function createIndivid($schedule, $times, $child = false)
    {
        $individ = new Individ();
        foreach ($times as $time) {
            $durations[$time->getId()] = $time->getDuration();
        }
        foreach ($schedule as $sc) {
            $individ->add($sc);
            $pat[] = array(
                "number" => $sc->getNumber(),
                "duration" => $durations[$sc->getWork()]
            );
        }
        $morning = $this->getQueueTime($pat, $individ, 'morning');
        $individ->setMorTime($morning["time"]);
        $individ->setGender(rand(0, 1000) % 2);
        $individ->setMorNumOfPat($morning["PatNumber"]);
        $evening = $this->getQueueTime($pat, $individ, 'evening');
        $individ->setEvTime($evening["time"]);
        $individ->setEvNumOfPat($evening["PatNumber"]);
        $individ->setNumOfPat($individ->getMorNumOfPat() + $individ->getEvNumOfPat());
        $individ->setAverQueueTime($this->getSumQueueTime($individ, $pat));
        $individ->setSuitable($this->isSuitable($individ));
        return $individ;
    }

    private function getSumQueueTime(Individ $individ, $patients)
    {

        $time = 0;
        for ($i = 1; $i < $individ->getNumOfPat() + 1; $i++) {
            $time += $patients[$i - 1]["duration"] * ($individ->getNumOfPat() - $i);
        }
        return $time / $individ->getNumOfPat();
    }

    private function getQueueTime($patients, Individ $individ, $time = 'morning')
    {
        $totalTime = 0;
        $numPat = 0;
        switch ($time) {
            case 'morning':
                for ($i = 0; $i < $individ->getLunchPos(); $i++) {
                    if ($patients[$i]) {
                        $numPat++;
                        $totalTime += $patients[$i]["duration"];
                    }
                }
                break;
            case 'evening':
                for ($i = $individ->getLunchPos(); $i < count($patients); $i++) {
                    if ($patients[$i]) {
                        $numPat++;
                        $totalTime += $patients[$i]["duration"];
                    }
                }
                break;
        }
        return array(
            "time" => $totalTime,
            "PatNumber" => $numPat
        );
    }
}
