<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReferralController extends Controller
{
    protected static function getReferrers()
    {
        return array(
            'i' => 'Instagram',
            's' => 'Strava',
            'r' => 'Reddit',
	    'n' => 'Nico'
        );
    }

    public function indexAction(Request $request, $referrer)
    {
        /** @var Session $session */
        $session = $this->get('session');

        $allowedReferrers = self::getReferrers();

        if (array_key_exists($referrer, $allowedReferrers)) {
            $referrerName = $allowedReferrers[$referrer];
        } else {
            throw new NotFoundHttpException();
        }

        $session->set('MANUAL_REFERRER', $referrerName);

        return $this->redirectToRoute('homepage');
    }
}
