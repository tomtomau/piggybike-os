<?php

namespace StravaBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Strava\API\Service\REST;

class AthleteService
{
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

    /**
     * @param REST $client
     *
     * @return ArrayCollection
     */
    public function listAthleteFollowers(REST $client)
    {
        $perPage = 200;
        $followers = new ArrayCollection();
        $page = 1;

        do {
            $lastResults = $client->getAthleteFollowers(null, $page, $perPage);

            foreach ($lastResults as $lastResult) {
                if (!$followers->contains($lastResult)) {
                    $followers->set($lastResult['id'], $lastResult);
                }
            }

            ++$page;
        } while (count($lastResults) === $perPage);

        return $followers;
    }
}
