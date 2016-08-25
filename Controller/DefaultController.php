<?php

namespace ScheduleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use GuideBundle\Form\PatientType;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        return $this->render('ScheduleBundle:Default:index.html.twig', array(
               )
        );
    }
}
