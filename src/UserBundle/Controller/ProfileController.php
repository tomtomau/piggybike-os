<?php

namespace UserBundle\Controller;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use BCC\ResqueBundle\Resque;
use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use StravaBundle\Jobs\ClassifyActivitiesJob;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class ProfileController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request)
    {

        // replace this example code with whatever you need
        return $this->render('UserBundle:Profile:view.html.twig', array('user' => $this->getUser()));
    }

    /**
     * Set home lat/lng.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction(Request $request)
    {
        $user = $this->getUser();

        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $suggestedLocations = $activityRepository->getSuggestedLocations($user);

        $form = $this->createFormBuilder($user)
            ->add('homeLat', NumberType::class, array('scale' => 3, 'label' => 'Home Latitude'))
            ->add('homeLng', NumberType::class, array('scale' => 3, 'label' => 'Home Longitude'))
            ->add('submit', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-default')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->get('user_bundle.user_repository');
            $userRepository->updateUser($user);

            /** @var MixpanelService $mixpanel */
            $mixpanel = $this->get('mixpanel');

            $mixpanel->addEvent(new Event('Set Home'));

            return $this->redirectToRoute($this->determineRedirectRoute($user));
        }

        return $this->render('UserBundle:Profile:home.html.twig',
            array(
                'user' => $user,
                'form' => $form->createView(),
                'suggested_locations' => $suggestedLocations,
            )
        );
    }

    /**
     * Set work lat/lng.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('workLat', NumberType::class, array('scale' => 3, 'label' => 'Work Latitude'))
            ->add('workLng', NumberType::class, array('scale' => 3, 'label' => 'Work Longitude'))
            ->add('submit', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-default')))
            ->getForm();

        $form->handleRequest($request);

        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $suggestedLocations = $activityRepository->getSuggestedLocations($user);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->get('user_bundle.user_repository');
            $userRepository->updateUser($user);

            /** @var MixpanelService $mixpanel */
            $mixpanel = $this->get('mixpanel');

            $mixpanel->addEvent(new Event('Set Work'));

            return $this->redirectToRoute($this->determineRedirectRoute($user));
        }

        return $this->render('UserBundle:Profile:work.html.twig', array('user' => $user, 'form' => $form->createView(), 'suggested_locations' => $suggestedLocations));
    }

    /**
     * Set cost for a commute.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setCostAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('cost', NumberType::class, array(
                'label' => 'Average Cost',
                'scale' => 2,
                'attr' => array(
                    'placeholder' => '3.93',
                ),
                'invalid_message' => 'Enter an amount without the currency symbol',
                'constraints' => new GreaterThanOrEqual(array(
                        'value' => 0,
                        'message' => 'Your savings should be a positive number',
                    )
                ), ))
            ->add('currency', CurrencyType::class, array(
                'preferred_choices' => array('AUD', 'NZD', 'USD', 'EUR', 'GBP'),
            ))
            ->add('submit', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-default')));

        // If the very first time we're setting cost

        if ($user->getGrowthOptin() === null) {
            $user->setGrowthOptin(true);
        }

        if (null === $user->getCost() || 1) {
            $form->add('growth_optin', CheckboxType::class, array(
                'label' => "Help #PiggyBike grow*",
                'required' => false
            ));
        }

        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->get('user_bundle.user_repository');
            $userRepository->updateUser($user);

            /** @var MixpanelService $mixpanel */
            $mixpanel = $this->get('mixpanel');

            $mixpanel->addEvent(new Event('Set Cost'));

            if ($form->has('growth_optin')) {
                $event = new Event($user->getGrowthOptin() ? 'Growth Optin' : 'Growth Optout');
                $mixpanel->addEvent($event);
            }

            return $this->redirectToRoute($this->determineRedirectRoute($user));
        }

        return $this->render('UserBundle:Profile:set_cost.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public static function determineRedirectRoute(User $user)
    {
        if ($user->hasSetHome()) {
            if ($user->hasSetWork()) {
                if ($user->hasSetCost()) {
                    if ($user->hasSeenConfirmation()) {
                        return 'user.profile.view';
                    } else {
                        // Show them confirmation page
                        return 'user.onboard.complete';
                    }
                } else {
                    return 'user.profile.set_cost';
                }
            } else {
                return 'user.profile.set_work';
            }
        } else {
            // Need to redirect to setting home
            return 'user.profile.set_home';
        }
    }

    /**
     * Take all activities, remove classification and queue all for reclassification.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function reclassifyAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->canClassify()) {
            throw new ServiceUnavailableHttpException();
        }

        /** @var ActivityRepository $activityRepo */
        $activityRepo = $this->get('activity.activity_repository');

        $activities = $activityRepo->findActivitiesForFeed($user);

        foreach ($activities as $activity) {
            /* @var Activity $activity */
            $activity->setClassifiedAt(null);
            $activity->setClassification(null);
        }

        $activityRepo->saveAll($activities);

        // only do this, just the once
        /** @var Resque $resque */
        $resque = $this->get('bcc_resque.resque');

        $job = new ClassifyActivitiesJob();
        $job->args = array(
            'user_id' => $user->getId(),
        );

        // Queue up that we need to go and classify this user
        $resque->enqueue($job);

        $this->addFlash('success', 'Reclassification of all rides triggered in the background. Check the activity list
        in a few moments');

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');

        $mixpanel->addEvent(new Event('Reclassify'));
        $mixpanel->identify($user);
        $mixpanel->dumpEventBag();

        return $this->redirectToRoute('activity.activity.list');
    }

    /**
     * Set cost for a commute.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setNotificationsAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('monthlyEmailOptOut', CheckboxType::class, array(
                'label' => 'Opt out of monthly update email',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-default')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->get('user_bundle.user_repository');
            $userRepository->updateUser($user);

            /** @var MixpanelService $mixpanel */
            $mixpanel = $this->get('mixpanel');

            if ($user->isMonthlyEmailOptOut()) {
                $mixpanel->addEvent(new Event('Opt Out'));
            } else {
                $mixpanel->addEvent(new Event('Opt In'));
            }

            return $this->redirectToRoute($this->determineRedirectRoute($user));
        }

        return $this->render('UserBundle:Profile:set_notifications.html.twig', array('form' => $form->createView()));
    }
}
