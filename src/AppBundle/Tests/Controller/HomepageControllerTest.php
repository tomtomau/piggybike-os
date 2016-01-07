<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Should have the logo
        $this->assertContains('piggy.bike', $crawler->filter('h1')->text());

        // Should have the login button
        $this->assertNotNull($crawler->filter('a.btn-strava'), 'Could not find the Strava button');
    }
}
