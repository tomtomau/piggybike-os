<?php

namespace ActivityBundle\Command;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use BCC\ResqueBundle\Resque;
use StravaBundle\Jobs\ClassifyActivitiesJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class ReclassifyUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('activities:reclassify')
            ->setDescription('Reclassify all for a user')
            ->addArgument('user_id', InputArgument::REQUIRED, 'User Id to reclassify')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getContainer()->get('user_bundle.user_repository');

        $user = $userRepo->find($input->getArgument('user_id'));

        if ($user instanceof User) {
            /** @var ActivityRepository $activityRepo */
            $activityRepo = $this->getContainer()->get('activity.activity_repository');

            $activities = $activityRepo->findActivitiesForFeed($user);

            foreach ($activities as $activity) {
                $activity->setClassifiedAt(null);
                $activity->setClassification(null);
            }

            $activityRepo->saveAll($activities);

            $output->writeln('Unset classifications');

            // only do this, just the once
            /** @var Resque $resque */
            $resque = $this->getContainer()->get('bcc_resque.resque');

            $job = new ClassifyActivitiesJob();
            $job->args = array(
                'user_id' => $user->getId(),
            );

            $resque->enqueue($job);
            $output->writeln('Queued up classification job');
        } else {
            throw new \Exception('Cannot find user');
        }
    }
}
