<?php

namespace AppBundle\Controller;

use MixpanelBundle\Mixpanel\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends Controller
{
    public function indexAction(Request $request)
    {
        //        $event = new Event('Home', []);
//        $this->get('mixpanel')->addEvent($event);

        return $this->render('AppBundle:Homepage:index.html.twig', array());
    }
}
