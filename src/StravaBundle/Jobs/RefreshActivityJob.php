<?php

namespace StravaBundle\Jobs;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\ActivitySyncService;
use BCC\ResqueBundle\ContainerAwareJob;
use StravaBundle\Services\ClientFactory;
use Symfony\Bridge\Monolog\Logger;
use UserBundle\Entity\User;

class RefreshActivityJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'refresh_activity';
    }

    public function run($args)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $container = $this->getContainer();

        // Check args
        if ($this->checkArgs($args)) {
            // Got id
            // Check whether we already have this activity id

            /** @var ActivityRepository $activityRepository */
            $activityRepository = $container->get('activity.activity_repository');

            $activity = $activityRepository->find($args['id']);

            if (!$activity instanceof Activity) {
                // We don't have this yet?!
                $logger->addCritical(sprintf('Received refresh for %s', $args['id']));
            } else {
                $user = $activity->getUser();

                /** @var ActivitySyncService $syncService */
                $syncService = $this->getContainer()->get('activity.activity_sync');
                $syncService->refreshActivity($activity, $this->generateClientFromUser($user));
            }
        } else {
            $logger->addCritical(sprintf('Dodgy queue message! %s', print_r($args, true)));
        }
    }

    /**
     * @param $args
     *
     * @return bool
     */
    protected function checkArgs($args)
    {
        return
            array_key_exists('id', $args) && is_int($args['id'])
            ;
    }

    /**
     * @param User $user
     *
     * @return \Strava\API\Service\REST
     */
    protected function generateClientFromUser(User $user)
    {
        /** @var ClientFactory $clientFactory */
        $clientFactory = $this->getContainer()->get('strava.client_factory');

        return $clientFactory->createClientFromAccessToken($user->getAccessToken());
    }
}
