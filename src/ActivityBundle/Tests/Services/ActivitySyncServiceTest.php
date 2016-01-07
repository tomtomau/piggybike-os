<?php

namespace ActivityBundle\Tests\Services;

use ActivityBundle\Entity\Activity;
use ActivityBundle\Services\ActivitySyncService;

class ActivitySyncServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSyncActivitiesBetween()
    {
        $mockActivityRepository = $this->getMockArtefactRepository(['hasResourceWithId', 'save']);
        $mockJSONToActivityTransformer = $this->getMockJSONToActivityTransformer(['transform']);
        $mockClassifier = $this->getMockClassifier(['classifyActivity']);
        $mockMixpanelService = $this->getMockMixpanelService(['setOnce']);
        $mockUserRepo = $this->getMockUserRepository();
        $mockActivityService = $this->getMockActivityService();

        $activitySyncService = new ActivitySyncService(
            $mockActivityRepository,
            $mockJSONToActivityTransformer,
            $mockClassifier,
            $mockMixpanelService,
            $mockActivityService,
            $mockUserRepo
        );

        $mockUser = $this->getMockUser(['canClassify', 'getCost']);
        $mockRESTClient = $this->getMockRESTClient(
            ['getAthleteActivities']
        );

        $mockUser->expects($this->any())
            ->method('canClassify')
            ->willReturn(true);

        $mockCost = 4.50;

        $mockUser->expects($this->any())
            ->method('getCost')
            ->willReturn($mockCost);

        $before = new \DateTime('+1 week');
        $after = new \DateTime('-1 week');

        $mockActivityId = rand(1, 9999);

        $mockActivityJSON = array(
            'id' => $mockActivityId,
        );

        $mockActivity = new Activity();
        $mockActivity->setManual(false);

        $mockJSONToActivityTransformer->expects($this->once())
            ->method('transform')
            ->with($mockActivityJSON)
            ->willReturn($mockActivity)
            ;

        $mockRESTClient->expects($this->once())
            ->method('getAthleteActivities')
            ->with($before->getTimestamp(), $after->getTimestamp(), 1, 200)
            ->willReturn(array($mockActivityJSON))
        ;

        $mockActivityRepository->expects($this->once())
            ->method('hasResourceWithId')
            ->with($mockActivityId)
            ->willReturn(false)
            ;

        $mockActivityRepository->expects($this->exactly(2))
            ->method('save')
            ->with($mockActivity)
            ;

        $mockClassifier->expects($this->once())
            ->method('classifyActivity')
            ->with($mockActivity, $mockUser)
            ->willReturn(Activity::CLASSIFY_COMMUTE_IN)
            ;

        $mockMixpanelService->expects($this->once())
            ->method('setOnce')
            ->with($mockUser, $this->arrayHasKey('First Classification'));

        $activitySyncService->syncActivitiesBetween(
            $mockUser, $mockRESTClient, $before, $after
        );

        $this->assertNotNull($mockActivity->getClassifiedAt(), 'classifiedAt should not be null');
        $this->assertEquals(Activity::CLASSIFY_COMMUTE_IN, $mockActivity->getClassification());
        $this->assertEquals($mockCost, $mockActivity->getValue());
    }

    protected function getMockRESTClient(array $methods = array())
    {
        return $this->getMockBuilder('Strava\API\Service\REST')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMockUser(array $methods = array())
    {
        return $this->getMockBuilder('UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     *
     * @return \ActivityBundle\Repository\ActivityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockArtefactRepository(array $methods = array())
    {
        return $this->getMockBuilder('ActivityBundle\Repository\ActivityRepository')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     *
     * @return \ActivityBundle\Services\JSONToActivityTransformer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockJSONToActivityTransformer(array $methods = array())
    {
        return $this->getMockBuilder('ActivityBundle\Services\JSONToActivityTransformer')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     *
     * @return \ActivityBundle\Services\ClassifierService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockClassifier(array $methods = array())
    {
        return $this->getMockBuilder('ActivityBundle\Services\ClassifierService')
        ->disableOriginalConstructor()
        ->disableProxyingToOriginalMethods()
        ->setMethods($methods)
        ->getMock();
    }

    /**
     * @param array $methods
     *
     * @return \MixpanelBundle\Services\MixpanelService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockMixpanelService(array $methods = array())
    {
        return $this->getMockBuilder('MixpanelBundle\Services\MixpanelService')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMockActivityService() {
        return $this->getMockBuilder('StravaBundle\Services\ActivityService')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
    }

    protected function getMockUserRepository() {
        return $this->getMockBuilder('UserBundle\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
    }
}
