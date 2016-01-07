<?php

namespace ActivityBundle\Services;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use Strava\API\Service\REST;
use StravaBundle\Services\ActivityService;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class ActivitySyncService
{
    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var JSONToActivityTransformer
     */
    protected $transformerService;

    /**
     * @var MixpanelService
     */
    protected $mixpanelService;

    /** @var ActivityService */
    protected $activityService;

    protected $userService;

    public function __construct(ActivityRepository $activityRepository,
                                JSONToActivityTransformer $transformerService,
                                ClassifierService $classifierService,
                                MixpanelService $mixpanelService,
                                ActivityService $activityService,
                                UserRepository $userRepository
    ) {
        $this->activityRepository = $activityRepository;
        $this->transformerService = $transformerService;
        $this->classifierService = $classifierService;
        $this->mixpanelService = $mixpanelService;
        $this->activityService = $activityService;
        $this->userRepo = $userRepository;
    }

    /**
     * @TODO - this needs to handle pagination
     *
     * @param User      $user
     * @param REST      $client
     * @param \DateTime $after
     *
     * @return ArrayCollection
     */
    public function syncActivitiesSince(User $user, REST $client, \DateTime $after)
    {
        $activities = $client->getAthleteActivities(null, $after->getTimestamp(), 1, 100);

        $syncedActivities = new ArrayCollection();

        foreach ($activities as $activityJSON) {
            if (false === $this->activityRepository->hasResourceWithId($activityJSON['id'])) {
                // We don't have this!
                // @TODO: Should this move?

                $activity = $this->addActivity($activityJSON, $user, $client);

                $syncedActivities->add($activity);
            }
        }

        return $syncedActivities;
    }

    public function syncActivitiesBetween(User $user, REST $client, \DateTime $before = null, \DateTime $after = null)
    {
        $perPage = 200; // Super high!

        $page = 1;

        $continue = true;

        while ($continue) {
            $activities = $client->getAthleteActivities($before->getTimestamp(), $after->getTimestamp(), $page, $perPage);

            // Continue if we've had exactly the same number of responses
            $continue = $perPage === count($activities);
            ++$page;

            foreach ($activities as $activityJSON) {
                if (false === $this->activityRepository->hasResourceWithId($activityJSON['id'])) {
                    // We don't have this!
                    $this->addActivity($activityJSON, $user, $client);
                }
            }
        }
    }

    /**
     * @param User $user
     * @param REST $client
     * @param int  $activityId
     *
     * @return Activity
     */
    public function syncActivity(User $user, REST $client, int $activityId) : Activity
    {
        $activityJSON = $client->getActivity($activityId);

        if (null === $activityJSON || !count($activityJSON) || !is_array($activityJSON)) {
            // Dud response?

            return;
        }

        // Ok, so it's formatted like how Strava provides it
        $activity = $this->addActivity($activityJSON, $user, $client);

        return $activity;
    }

    /**
     * @param array $activityJSON
     * @param User  $user
     *
     * @return Activity
     */
    protected function addActivity(array $activityJSON, User $user, REST $client) : Activity
    {
        $activity = $this->transformerService->transform($activityJSON);

        $activity->setUser($user);

        $this->activityRepository->save($activity);

        if ($user->canClassify() && null !== $user->getCost() && !$activity->isManual()) {
            // Can classify!

            $classification = $this->classifierService->classifyActivity($activity, $user);

            $activity
                ->setClassifiedAt(new \DateTime())
                ->setClassification($classification)
                ->setValue(Activity::CLASSIFY_COMMUTE_NO === $classification ? 0 : $user->getCost())
            ;

            $this->activityRepository->save($activity);

            if (Activity::CLASSIFY_COMMUTE_NO !== $classification) {
                $this->mixpanelService->setOnce($user, array(
                    'First Classification' => (new \DateTime())->format('c'),
                ));

                // Test if this is the first for the user
                if (true === $user->getGrowthOptin() && !$user->isHaveAppendedHashtag()){

                    try {
                        if ($this->activityService->appendHashtag($client, $activity->getResourceId())) {
                            // Update the user
                            $user->setHaveAppendedHashtag(true);
                            $this->userRepo->updateUser($user);

                            $this->mixpanelService->addEvent(new Event("Append Hashtag", array('id' => $activity->getResourceId())));
                            $this->mixpanelService->dumpEventBag();
                        }
                    } catch (\Pest_Exception $e) {
                        // TODO: Do something?
                    }


                    // Set the hashtag
                    $this->mixpanelService->setOnce($user, array(
                        'Set Hashtag' => 1
                    ));
                }
            }
        }

        return $activity;
    }

    /**
     * @param Activity $activity
     *
     * @return Activity
     */
    public function refreshActivity(Activity $activity, REST $client) : Activity
    {
        $refreshedActivityJSON = $client->getActivity($activity->getResourceId());

        // @TODO: Should have a bi-directional transformer maybe?

        $refreshedActivity = $this->transformerService->transform($refreshedActivityJSON);

        // Updates name
        if ($refreshedActivity->getName() !== $activity->getName()) {
            $activity->setName($refreshedActivity->getName());
        }

        // Updates polyline
        if ($refreshedActivity->getPolyline() !== $activity->getPolyline()) {
            $activity->setPolyline($refreshedActivity->getPolyline());
        }

        $this->activityRepository->save($activity);

        return $activity;
    }
}
