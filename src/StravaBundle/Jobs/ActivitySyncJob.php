<?php

namespace StravaBundle\Jobs;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\ActivitySyncService;
use BCC\ResqueBundle\ContainerAwareJob;
use BCC\ResqueBundle\Resque;
use StravaBundle\Services\ClientFactory;
use Symfony\Bridge\Monolog\Logger;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class ActivitySyncJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'activity_sync';
    }

    public function run($args)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $container = $this->getContainer();

        // Check args
        if ($this->checkArgs($args)) {
            // Got owner_id and object_id
            // Check whether we already have this activity id

            /** @var ActivityRepository $activityRepository */
            $activityRepository = $container->get('activity.activity_repository');

            $activity = $activityRepository->find($args['object_id']);

            if ($activity instanceof Activity) {
                $logger->addCritical(sprintf('Received push for %d, but it already exists', $activity->getId()));
            } else {
                // We don't have this yet, just double check we do have this user
                /** @var UserRepository $userRepository */
                $userRepository = $container->get('user_bundle.user_repository');

                $user = $userRepository->findByUsername($args['owner_id']);

                if ($user instanceof User) {
                    // We do have this user!

                    /** @var ActivitySyncService $syncService */
                    $syncService = $this->getContainer()->get('activity.activity_sync');
                    $response = $syncService->syncActivity($user, $this->generateClientFromUser($user), $args['object_id']);

                    if ($response instanceof Activity) {
                        // Success!

                        /** @var Resque $resque */
                        $resque = $this->getContainer()->get('bcc_resque.resque');

                        // Add another job to refresh in some time
                        $refreshJob = new RefreshActivityJob();

                        $refreshJob->args = [
                            'id' => $response->getId(),
                        ];

                        $resque->enqueueAt(new \DateTime('+30 minutes'), $refreshJob);

                        $logger->addInfo(sprintf('Successfully synced ride %d', $response->getResourceId()));
                    } else {
                        $logger->addCritical(sprintf('Failed to sync ride %d', $response->getResourceId()));
                    }
                } else {
                    // Hmm. We don't have this user?
                    $logger->addCritical(sprintf("Received push for user %d but we don't have them in our system?", $args['owner_id']));
                }
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
            array_key_exists('owner_id', $args) && is_int($args['owner_id']) &&
            array_key_exists('object_id', $args) && is_int($args['object_id'])
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
