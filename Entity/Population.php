<?php
/**
 * Created by PhpStorm.
 * User: Eleonora
 * Date: 14.05.2016
 * Time: 20:19
 */

namespace ScheduleBundle\Entity;
use ScheduleBundle\Entity\Individ;


/**
 * Class Population
 * @package ScheduleBundle\Entity
 */
class Population
{
    /**
     * @var
     */
    public $individs;

    public $size;

    public $maxMorLength;

    public $maxEvLength;

    /**
     * Population constructor.
     * @param $size
     */
    public function __construct()
    {
        $this->size = 10;
    }

    public function getMostWeak()
    {
        $max = 0;
        $weak = new Individ();
        foreach ($this->getIndivids() as $key => $individ) {
            if ($individ->getAverQueueTime() > $max) {
                $max = $individ->getAverQueueTime();
                $weak = $individ;
            }
        }
        return $weak;
    }

    public function warNeed()
    {
        $girls = 0;
        $boys = 0;
        foreach ($this->getIndivids() as $individ) {
            if ($individ->getGender() === 1) {
                $girls++;
            } else {
                $boys++;
            }
        }
        if ($boys / count($this->getIndivids()) > 0.7) {
            return true;
        } else {
            return false;
        }
    }

    public function getMaxMorLength()
    {
        $max = 0;
        foreach ($this->getIndivids() as $individ) {
            if ($individ->getMorNumOfPat() > $max) {
                $max = $individ->getMorNumOfPat();
            }
        }
        $this->maxMorLength = $max;
    }

    public function getSumBoysTime()
    {
        $sum = 0;
        foreach ($this->getIndivids() as $individ) {
            if ($individ->getGender() === 0) {
                $sum += $individ->getAverQueueTIme();
            }
        }
        return $sum;
    }

    public function getMaxEvLength()
    {
        $max = 0;
        foreach ($this->getIndivids() as $individ) {
            if ($individ->getEvNumOfPat() > $max) {
                $max = $individ->getEvNumOfPat();
            }
        }
        $this->maxEvLength = $max;
    }
    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getIndivids()
    {
        return $this->individs;
    }

    /**
     * @param mixed $schedules
     */
    public function addIndivid(Individ $individ)
    {
        $this->individs[] = $individ;
    }
}