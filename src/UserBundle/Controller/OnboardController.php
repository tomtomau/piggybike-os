<?php

namespace UserBundle\Controller;

use ActivityBundle\Repository\ActivityRepository;
use BCC\ResqueBundle\Resque;
use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use StravaBundle\Jobs\ClassifyActivitiesJob;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class OnboardController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        if ($user->isSetup()) {
            return $this->redirectToRoute('user.onboard.complete');
        }

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');

        $mixpanel->identify($user, 0);

        /** @var Session $session */
        $session = $this->get('session');

        $justRegistered = $session->get('just_registered', false) ? true : false;

        if ($justRegistered) {
            $session->remove('just_registered');
        }

        return $this->render('UserBundle:Onboard:index.html.twig',
            array(
                'just_registered' => $justRegistered,
                'continue_route' => ProfileController::determineRedirectRoute($this->getUser()),
            )
        );
    }

    /**
     * @return Response
     */
    public function completeAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isSetup()) {
            return $this->redirectToRoute('user.onboard.index');
        }

        if ($user->hasSeenConfirmation()) {
            return $this->redirectToRoute('activity.activity.list');
        }

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');

        $mixpanel->addEvent(new Event('Setup Complete'));

        $user->setSeenConfirmation(new \DateTime());

        /** @var UserRepository $userRepository */
        $userRepository = $this->get('user_bundle.user_repository');
        $userRepository->updateUser($user);

        // only do this, just the once
        /** @var Resque $resque */
        $resque = $this->get('bcc_resque.resque');

        $job = new ClassifyActivitiesJob();
        $job->args = array(
            'user_id' => $this->getUser()->getId(),
        );

        $resque->enqueue($job);

        return $this->render('UserBundle:Onboard:complete.html.twig', array()

        );
    }

    /*
     * AJAX Methods
     */

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getPointsOfInterestAction(Request $request)
    {
        $user = $this->getUser();

        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $points = $activityRepository->getSuggestedLocations($user);

        $response = array(
            'center' => self::generateMapCenter($points),
            'points' => $points,
        );

        return new JsonResponse($response);
    }

    /**
     * Source: http://stackoverflow.com/questions/6671183/calculate-the-center-point-of-multiple-latitude-longitude-coordinate-pairs.
     *
     * @param array $points
     *
     * @return array
     */
    protected static function generateMapCenter(array $points = array())
    {
        $num_coords = count($points);

        $X = 0.0;
        $Y = 0.0;
        $Z = 0.0;

        foreach ($points as $coord) {
            $lat = $coord['lat'] * pi() / 180;
            $lon = $coord['lng'] * pi() / 180;

            $a = cos($lat) * cos($lon);
            $b = cos($lat) * sin($lon);
            $c = sin($lat);

            $X += $a;
            $Y += $b;
            $Z += $c;
        }

        $X /= $num_coords;
        $Y /= $num_coords;
        $Z /= $num_coords;

        $lon = atan2($Y, $X);
        $hyp = sqrt($X * $X + $Y * $Y);
        $lat = atan2($Z, $hyp);

        return array('latitude' => $lat * 180 / pi(), 'longitude' => $lon * 180 / pi());
    }
}
