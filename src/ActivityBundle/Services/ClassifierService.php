<?php

namespace ActivityBundle\Services;

use ActivityBundle\Entity\Activity;
use UserBundle\Entity\User;

/**
 * Class ClassifierService
 * - So was that a ride to work?
 *
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ClassifierService
{
    public function classifyActivity(Activity $activity, User $user)
    {
        // Check if start is at work, and end is at home (the best commute)
        if ($this->pointNearWork($user, $activity->getStartLat(), $activity->getStartLng(), 1) && $this->pointNearHome($user, $activity->getEndLat(), $activity->getEndLng(), 1)) {
            return Activity::CLASSIFY_COMMUTE_OUT;
        }

        // Check if end is at work, and start is at home
        if ($this->pointNearWork($user, $activity->getEndLat(), $activity->getEndLng(), 1) && $this->pointNearHome($user, $activity->getStartLat(), $activity->getStartLng(), 1)) {
            return Activity::CLASSIFY_COMMUTE_IN;
        }

        // Probably not a commute...
        return Activity::CLASSIFY_COMMUTE_NO;
    }

    /**
     * Source: https://www.geodatasource.com/developers/php.
     *
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     *
     * @return float
     */
    protected function distanceBetweenPoints($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1.609344;
    }

    /**
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @param $threshold
     *
     * @return bool
     */
    protected function pointNearPoint($lat1, $lon1, $lat2, $lon2, $threshold)
    {
        $distance = $this->distanceBetweenPoints($lat1, $lon1, $lat2, $lon2);

        return $distance < $threshold;
    }

    /**
     * @param User $user
     * @param $lat
     * @param $lon
     * @param $threshold
     *
     * @return bool
     */
    protected function pointNearHome(User $user, $lat, $lon, $threshold)
    {
        return $this->pointNearPoint($user->getHomeLat(), $user->getHomeLng(), $lat, $lon, $threshold);
    }

    /**
     * @param User $user
     * @param $lat
     * @param $lon
     * @param $threshold
     *
     * @return bool
     */
    protected function pointNearWork(User $user, $lat, $lon, $threshold)
    {
        return $this->pointNearPoint($user->getWorkLat(), $user->getWorkLng(), $lat, $lon, $threshold);
    }
}
