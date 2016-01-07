<?php

namespace StravaBundle\Jobs;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\ClassifierService;
use BCC\ResqueBundle\ContainerAwareJob;
use Symfony\Bridge\Monolog\Logger;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class ClassifyActivitiesJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'classify_activities';
    }

    public function run($args)
    {
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $container = $this->getContainer();

        // Check args
        if ($this->checkArgs($args)) {

            /** @var UserRepository $userRepository */
            $userRepository = $container->get('user_bundle.user_repository');

            $user = $userRepository->find($args['user_id']);

            if ($user instanceof User) {
                if ($user->canClassify()) {
                    /** @var ActivityRepository $activityRepository */
                    $activityRepository = $container->get('activity.activity_repository');

                    $activities = $activityRepository->findActivitiesToBeClassifiedForUser($user);

                    foreach ($activities as $activity) {
                        /** @var ClassifierService $classifierService */
                        $classifierService = $container->get('activity.classifier');

                        $classification = $classifierService->classifyActivity($activity, $activity->getUser());

                        $activity
                            ->setClassifiedAt(new \DateTime())
                            ->setClassification($classification)
                            ->setValue(Activity::CLASSIFY_COMMUTE_NO === $classification ? 0 : $user->getCost())
                        ;

                        $activityRepository->save($activity);
                    }
                } else {
                    $logger->addCritical(sprintf('User cannot classify (%d)', $args['user_id']));
                }
            } else {
                $logger->addCritical(sprintf('Cannot find user from id %d', $args['user_id']));
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
            array_key_exists('user_id', $args) && is_int($args['user_id'])
            ;
    }
}
