<?php

namespace StravaBundle\Jobs;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Services\ActivitySyncService;
use BCC\ResqueBundle\ContainerAwareJob;
use StravaBundle\Services\ClientFactory;
use Symfony\Bridge\Monolog\Logger;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

/**
 * Class ActivityLoadJob
 * - This job is used when the user first signs up
 *
 * @package StravaBundle\Jobs
 * @author Tom Newby <me@tomnewby.net>
 */
class ActivityLoadJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'activity_load';
    }

    public function run($args)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $container = $this->getContainer();

        if ($this->checkArgs($args)) {
            $afterDate = new \DateTime($args['after_date']);
            $beforeDate = new \DateTime($args['before_date']);

            // Find user
            $userId = $args['user'];

            /** @var UserRepository $userRepository */
            $userRepository = $container->get('user_bundle.user_repository');

            $user = $userRepository->find($userId);

            if ($user instanceof User) {
                // Let's call the service

                /** @var ActivitySyncService $syncService */
                $syncService = $this->getContainer()->get('activity.activity_sync');

                $client = $this->generateClientFromUser($user);
                $syncService->syncActivitiesBetween($user, $client, $beforeDate, $afterDate);
            } else {
                $logger->addCritical(sprintf("Can't find user %s", $userId));
            }
        } else {
            $logger->addCritical(sprintf('Bad args %s', print_r($args, true)));
        }
    }

    public function checkArgs($args)
    {
        return isset($args['user']) && is_int($args['user'])
            && isset($args['before_date']) && isset($args['after_date']);
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
