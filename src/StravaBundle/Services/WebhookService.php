<?php

namespace StravaBundle\Services;

use BCC\ResqueBundle\Resque;
use Monolog\Logger;
use StravaBundle\Jobs\ActivitySyncJob;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class WebhookService
 * - Pushing the work into a service so it can be tested!
 *
 * @author Tom Newby <tom.newby@redeye.co>
 */
class WebhookService
{
    /**
     * @var Resque
     */
    protected $resque;

    public function __construct(Resque $resque, Logger $logger)
    {
        $this->resque = $resque;
        $this->logger = $logger;
    }

    /**
     * @param array $params
     *
     * @return Response
     */
    public function handleWebhook(array $params)
    {
        if (array_key_exists('owner_id', $params) && array_key_exists('object_id', $params)) {
            $job = new ActivitySyncJob();
            $job->args = array(
                'owner_id' => $params['owner_id'],
                'object_id' => $params['object_id'],
            );

            $this->resque->enqueue($job);

            return new Response('Queued');
        }

        $this->logger->addCritical(sprintf('Bad request %s', print_r($params, true)));

        throw new BadRequestHttpException('Bad request!');
    }
}
