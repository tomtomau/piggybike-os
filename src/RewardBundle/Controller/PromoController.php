<?php

namespace RewardBundle\Controller;

use MixpanelBundle\Mixpanel\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoController extends Controller
{
    public function listAction(Request $request) {
        $this->get('mixpanel')->addEvent(new Event('Promo List'));

        return $this->render('RewardBundle:Promo:list.html.twig');
    }

    public function getPromoCountAction() {
        $now = new \DateTime();

        $couponEnds = new \DateTime('2016-12-15 23:59:59+1000');

        if ($now > $couponEnds) {
            return new Response("");
        } else{
            return new Response("<span class=\"new\">3</span>");
        }
    }
}