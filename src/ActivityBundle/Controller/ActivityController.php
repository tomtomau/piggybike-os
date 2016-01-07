<?php

namespace ActivityBundle\Controller;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\ActivitySyncService;
use ActivityBundle\Services\BalanceService;
use Mixpanel;
use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use StravaBundle\Services\ClientFactory;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use UserBundle\Entity\User;

class ActivityController extends Controller
{
    public function listAction(Request $request, $pageId = 1)
    {
        $pageId = intval($pageId);

        if ($pageId < 1) {
            return $this->redirectToRoute('activity.activity.list');
        }

        $user = $this->getUser();

        if (!$user->isSetup()) {
            return $this->redirectToRoute('user.onboard.index');
        }

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');

        $mixpanel->addEvent(new Event('Activity List'));

        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $perPage = 10;

        $activities = $activityRepository->findPaginatedActivitiesForFeed($user, $pageId, $perPage);

        $activityCount = $activityRepository->getActivityCount($user);

        /* @var BalanceService $balance */
        $balanceService = $this->get('activity.balance');

        $balance = $balanceService->getBalanceForUser($user);

        $adIndex = 2; // hard coded for now

        return $this->render('ActivityBundle:Activity:list.html.twig',
            array(
                'user' => $user,
                'activities' => $activities,
                'balance' => $balance,
                'page' => $pageId,
                'total_count' => $activityCount,
                'per_page' => $perPage,
                'show_next' => ($perPage * $pageId) < $activityCount,
                'show_previous' => $pageId > 1,
                'ad_index' => $adIndex
            )
        );
    }

    public function editAction(Request $request, $id)
    {
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $activity = $activityRepository->findUsersActivityById($this->getUser(), $id);

        if (!$activity instanceof Activity) {
            throw new NotFoundHttpException();
        }

        $form = $this->createFormBuilder($activity)
            //->add('classification')
            ->add('value', MoneyType::class, array(
                'scale' => 2,
                'currency' => $this->getUser()->getCurrency(),
                'constraints' => new GreaterThanOrEqual(array(
                    'value' => 0,
                    'message' => 'Your savings should be a positive number',
                )), ))
            ->add('classification', ChoiceType::class, array(
                'choices' => Activity::getClassificationOptions(),
                'choices_as_values' => true,
            ))
            ->add('submit', SubmitType::class, array('attr' => array('class' => 'btn btn-default')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activityRepository->save($activity);

            return $this->redirectToRoute('activity.activity.list');
        }

        return $this->render('ActivityBundle:Activity:edit.html.twig', array('activity' => $activity, 'form' => $form->createView()));
    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function refreshAction(int $id)
    {
        /** @var ActivityRepository $activityRepository */
        $activityRepository = $this->get('activity.activity_repository');

        $activity = $activityRepository->find($id);

        if (!$activity instanceof Activity || $activity->getUser() !== $this->getUser()) {
            /** @var Logger $logger */
            $logger = $this->get('logger');

            $logger->addCritical(sprintf('User %s tried to refresh %d', $this->getUser()->getId(), $id));
            throw new NotFoundHttpException();
        }

        /** @var ActivitySyncService $activitySyncService */
        $activitySyncService = $this->get('activity.activity_sync');

        /** @var ClientFactory $clientFactory */
        $clientFactory = $this->get('strava.client_factory');

        /** @var User $user */
        $user = $this->getUser();

        $client = $clientFactory->createClientFromAccessToken($user->getAccessToken());

        $refreshedActivity = $activitySyncService->refreshActivity($activity, $client);

        $this->addFlash('success', sprintf("Refreshed activity '%s'", $refreshedActivity->getName()));

        $this->get('mixpanel')->addEvent(new Event('Refresh Activity', ['Activity Id' => $refreshedActivity->getResourceId()]));

        return $this->redirectToRoute('activity.activity.list');
    }
}
