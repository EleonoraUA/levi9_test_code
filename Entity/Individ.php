<?php
/**
 * Created by PhpStorm.
 * User: Eleonora
 * Date: 14.05.2016
 * Time: 18:25
 */

namespace ScheduleBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use ScheduleBundle\Entity\Patient;


class Individ
{
    /**
     * @var array
     */
    private $patients;

    /**
     * @var int
     */
    private $morTime;

    private $suitable;

    /**
     * @var int
     */
    private $evTime;

    /**
     * @var int
     */
    private $numOfPat;

    /**
     * @var int
     */
    private $gender;

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @var int
     */
    private $morNumOfPat;

    /**
     * @var int
     */
    private $evNumOfPat;

    /**
     * @var float
     */
    private $averQueueTime;

    /**
     * @var int
     */
    private $lunchPos;

    /**
     * @var bool
     */
    private $reanimation;

    /**
     * @return boolean
     */
    public function isReanimation()
    {
        return $this->reanimation;
    }

    /**
     * @param boolean $reanimation
     */
    public function setReanimation($reanimation)
    {
        $this->reanimation = $reanimation;
    }


    /**
     * @return int
     */
    public function getLunchPos()
    {
        return $this->lunchPos;
    }

    /**
     * @return bool
     */
    public function getSuitable()
    {
        return $this->suitable;
    }

    /**
     * @param bool $suitable
     */
    public function setSuitable($suitable)
    {
        $this->suitable = $suitable;
    }

    /**
     * @param int $lunchPos
     */
    public function setLunchPos($lunchPos)
    {
        $this->lunchPos = $lunchPos;
    }

    public function __construct()
    {
        $this->patients = new ArrayCollection();
        $this->lunchPos = 9;
    }


    /**
     * @return array
     */
    public function getPatients()
    {
        return $this->patients;
    }

    /**
     * @param array $patients
     */
    public function add(Patient $patients)
    {
        $this->patients[] = $patients;
    }

    /**
     * @return int
     */
    public function getMorTime()
    {
        return $this->morTime;
    }

    /**
     * @param int $morTime
     */
    public function setMorTime($morTime)
    {
        $this->morTime = $morTime;
    }

    /**
     * @return int
     */
    public function getEvTime()
    {
        return $this->evTime;
    }

    /**
     * @param int $evTime
     */
    public function setEvTime($evTime)
    {
        $this->evTime = $evTime;
    }

    /**
     * @return int
     */
    public function getNumOfPat()
    {
        return $this->numOfPat;
    }

    /**
     * @param int $numOfPat
     */
    public function setNumOfPat($numOfPat)
    {
        $this->numOfPat = $numOfPat;
    }

    /**
     * @return int
     */
    public function getMorNumOfPat()
    {
        return $this->morNumOfPat;
    }

    /**
     * @param int $morNumOfPat
     */
    public function setMorNumOfPat($morNumOfPat)
    {
        $this->morNumOfPat = $morNumOfPat;
    }

    /**
     * @return int
     */
    public function getEvNumOfPat()
    {
        return $this->evNumOfPat;
    }

    /**
     * @param int $evNumOfPat
     */
    public function setEvNumOfPat($evNumOfPat)
    {
        $this->evNumOfPat = $evNumOfPat;
    }


    /**
     * @return float
     */
    public function getAverQueueTime()
    {
        return $this->averQueueTime;
    }

    /**
     * @param float $averQueueTime
     */
    public function setAverQueueTime($averQueueTime)
    {
        $this->averQueueTime = $averQueueTime;
    }


}