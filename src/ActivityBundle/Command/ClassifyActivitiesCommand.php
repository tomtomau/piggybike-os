<?php

namespace ActivityBundle\Command;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\ClassifierService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClassifyActivitiesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('activities:classify')
            ->setDescription('Classify all activities which have yet to be classified');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ActivityRepository $activityRepo */
        $activityRepo = $this->getContainer()->get('activity.activity_repository');

        $activities = $activityRepo->findActivitiesToBeClassified();

        $output->writeln(sprintf('Found %d activities', $activities->count()));

        /** @var ClassifierService $classifierService */
        $classifierService = $this->getContainer()->get('activity.classifier');

        foreach ($activities as $activity) {
            /* @var Activity $activity */
            $user = $activity->getUser();

            $cost = $user->getCost();

            if (null === $cost) {
                continue;
            }

            $classification = $classifierService->classifyActivity($activity, $activity->getUser());

            $activity
                ->setClassifiedAt(new \DateTime())
                ->setClassification($classification)
                ->setValue(Activity::CLASSIFY_COMMUTE_NO === $classification ? 0 : $cost)
            ;

            $activityRepo->save($activity);

            $output->writeln(sprintf('Set %d to %d', $activity->getId(), $activity->getClassification()));
        }
    }
}
