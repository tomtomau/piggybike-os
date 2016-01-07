<?php

namespace ActivityBundle\Command;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;

class HashtagMostRecentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('hashtag')
            ->setDescription('Append hashtag to most recent ride');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ActivityRepository $activityRepo */
        $activityRepo = $this->getContainer()->get('activity.activity_repository');

        $userRepo = $this->getContainer()->get('user_bundle.user_repository');
        $clientFactory = $this->getContainer()->get('strava.client_factory');
        $activityService = $this->getContainer()->get('strava.activity_service');

        /** @var User[] $users */
        $users = $userRepo->findAll();

        foreach ($users as $user) {
            $commute = $activityRepo->findMostRecentCommute($user);

            if (!($commute instanceof Activity)) continue;

            $accessToken = $user->getAccessToken();
            $client = $clientFactory->createClientFromAccessToken($accessToken);

            $activityService->appendHashtag($client, $commute->getResourceId());
            $output->writeln(sprintf("Appending for %s as", $commute->getResourceId()));
        }
    }
}