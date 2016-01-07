<?php

namespace StravaBundle\Services;

use Strava\API\Service\REST;

class ClientFactory
{
    /**
     * @param $accessToken
     *
     * @return REST
     */
    public function createClientFromAccessToken($accessToken)
    {
        $adapter = new \Pest('https://www.strava.com/api/v3');

        return new REST($accessToken, $adapter);
    }
}
