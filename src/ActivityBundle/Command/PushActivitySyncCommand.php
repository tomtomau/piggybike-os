<?php

namespace ActivityBundle\Command;

use ActivityBundle\Entity\Activity;
use BCC\ResqueBundle\Resque;
use StravaBundle\Jobs\ActivitySyncJob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushActivitySyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('activity:push')
            ->addArgument('owner_id', InputArgument::REQUIRED, 'Owner Id (Strava)')
            ->addArgument('object_id', InputArgument::REQUIRED, 'Object Id (Strava)')
            ->setDescription('Pushes an activity id into the queue to be processed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Resque $resque */
        $resque = $this->getContainer()->get('bcc_resque.resque');

        $job = new ActivitySyncJob();
        $job->args = array(
            'owner_id' => (int) $input->getArgument('owner_id'),
            'object_id' => (int) $input->getArgument('object_id'),
        );

        $resque->enqueue($job);

        $output->writeln('Added activity to queue');
    }
}
