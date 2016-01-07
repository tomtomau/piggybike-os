<?php

namespace StravaBundle\Tests\Services;

use StravaBundle\Services\WebhookService;

class WebhookServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWebhookBadRequest()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\BadRequestHttpException');

        $mockResque = $this->getMockResque();
        $mockLogger = $this->getMockLogger();

        $webhookService = new WebhookService($mockResque, $mockLogger);
        $webhookService->handleWebhook(array());
    }

    public function testHandleWebhookSuccess()
    {
        $mockResque = $this->getMockResque(array('enqueue'));
        $mockLogger = $this->getMockLogger();

        $webhookService = new WebhookService($mockResque, $mockLogger);

        $ownerId = 1234;
        $objectId = 2345;

        $mockResque
            ->expects($this->once())
            ->method('enqueue')
            ->with(
                $this->attributeEqualTo('args', array('owner_id' => $ownerId, 'object_id' => $objectId))
            );

        $webhookService->handleWebhook(
            array(
                'owner_id' => $ownerId,
                'object_id' => $objectId,
            )
        );
    }

    /**
     * @param array $methods
     *
     * @return \BCC\ResqueBundle\Resque|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockResque(array $methods = array())
    {
        return $this->getMockBuilder('BCC\ResqueBundle\Resque')
            ->disableProxyingToOriginalMethods()
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     *
     * @return \Monolog\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLogger(array $methods = array())
    {
        return $this->getMockBuilder('Monolog\Logger')
            ->disableProxyingToOriginalMethods()
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
