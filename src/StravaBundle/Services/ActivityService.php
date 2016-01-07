<?php

namespace StravaBundle\Services;

use Strava\API\Service\REST;

const HASHTAG = "#PiggyBike";

class ActivityService
{
    public function appendHashtag(REST $client, $activityId) {
        $activity = $client->getActivity($activityId);

        $name = $activity["name"];

        if (false === strpos($name, "#PiggyBike")) {
            $name .= " " . HASHTAG;

            $client->updateActivity($activityId, $name);

            return true;
        }

        return false;
    }

    /**
     * @param REST $client
     *
     * @return ArrayCollection
     */
    public function listAthleteFriends(REST $client)
    {
        $perPage = 200;
        $friends = new ArrayCollection();
        $page = 1;

        do {
            $lastResults = $client->getAthleteFriends(null, $page, $perPage);

            foreach ($lastResults as $lastResult) {
                if (!$friends->contains($lastResult)) {
                    $friends->set($lastResult['id'], $lastResult);
                }
            }

            ++$page;
        } while (count($lastResults) === $perPage);

        return $friends;
    }
}