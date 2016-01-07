<?php

namespace ActivityBundle\Command;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Services\ActivitySyncService;
use StravaBundle\Services\ClientFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class SyncUserActivitiesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('activities:sync:user')
            ->setDescription('Sync one user')
            ->addArgument('user_id', InputArgument::REQUIRED, 'User Id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('user_id');

        $user = $this->findUser($userId);

        $client = $this->generateClientFromUser($user);

        /** @var ActivitySyncService $syncService */
        $syncService = $this->getContainer()->get('activity.activity_sync');

        $output->writeln(sprintf('Found user %s', $user->getEmail()));

        $activities = $syncService->syncActivitiesSince($user, $client, new \DateTime('-4 week'));

        $activitiesString = implode("\n - ", $activities->map(function (Activity $activity) { return $activity->getName(); })->toArray());

        $output->writeln(sprintf("Found %d activities: \n - %s", $activities->count(), $activitiesString));
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

    /**
     * @param $userId
     *
     * @return User
     *
     * @throws \Exception
     */
    protected function findUser($userId)
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getContainer()->get('user_bundle.user_repository');
        $user = $userRepo->find($userId);

        if ($user instanceof User) {
            return $user;
        } else {
            throw new \Exception(sprintf('Could not find user with id %s', $userId));
        }
    }
}
