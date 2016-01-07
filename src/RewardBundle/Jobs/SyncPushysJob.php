<?php

namespace RewardBundle\Jobs;

use BCC\ResqueBundle\ContainerAwareJob;

class SyncPushysJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'sync_pushys';
    }

    public function run($args)
    {
        $this->getContainer()->get('reward.pushys')->syncProducts();

        $this->getContainer()->get('monolog.logger.slackinfo')->addInfo(
            "Synced Pushys products"
        );
    }
}