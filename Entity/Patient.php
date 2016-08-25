<?php

namespace ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Patient
 *
 * @ORM\Table(name="patient")
 * @ORM\Entity(repositoryClass="ScheduleBundle\Repository\PatientRepository")
 */
class Patient
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer", unique=true)
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="work", type="integer")
     */
    private $work;

    /**
     * Patient constructor.
     * @param int $number
     * @param int $work
     */
    public function __construct($number, $work)
    {
        $this->number = $number;
        $this->work = $work;
       // $this->mutated = NULL;
    }

    public $mutated;

    /**
     * @return mixed
     */
    public function getMutated()
    {
        return $this->mutated;
    }

    /**
     * @param mixed $mutated
     */
    public function setMutated($mutated)
    {
        $this->mutated = $mutated;
    }

    /**
     * @return mixed
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * @param mixed $selection
     */
    public function setSelection($selection)
    {
        $this->selection = $selection;
    }

    /**
     * @return mixed
     */
    public function getReanimation()
    {
        return $this->reanimation;
    }

    /**
     * @param mixed $reanimation
     */
    public function setReanimation($reanimation)
    {
        $this->reanimation = $reanimation;
    }

    public $selection;

    public $reanimation;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return Patient
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set work
     *
     * @param integer $work
     *
     * @return Patient
     */
    public function setWork($work)
    {
        $this->work = $work;

        return $this;
    }

    /**
     * Get work
     *
     * @return int
     */
    public function getWork()
    {
        return $this->work;
    }
}

