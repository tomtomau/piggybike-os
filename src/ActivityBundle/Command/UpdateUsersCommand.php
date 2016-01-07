<?php

namespace ActivityBundle\Command;

use BCC\ResqueBundle\Resque;
use StravaBundle\Jobs\ActivityLoadJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

/**
 * Write a command which adds heaps of ActivityLoadJob (s) for all users
 *  - This is just gets everyone up to sync, because occasionally Strava fails.
 */
class UpdateUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('activities:update-all')
            ->setDescription('Update all users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getContainer()->get('user_bundle.user_repository');

        $users = $userRepo->findAll();

        foreach ($users as $user) {
            /* @var User $user */
            /** @var Resque $resque */
            $resque = $this->getContainer()->get('bcc_resque.resque');

            $loadJob = new ActivityLoadJob();

            $format = 'Y/m/d H:i:s';

            $loadJob->args = array(
                'user' => $user->getId(),
                'before_date' => (new \DateTime())->format($format),
                'after_date' => (new \DateTime('-1 week'))->format($format),
            );

            $resque->enqueue($loadJob);
        }
    }
}
