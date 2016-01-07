<?php

namespace MixpanelBundle\EventListener;

use ActivityBundle\Services\BalanceService;
use Mixpanel;
use MixpanelBundle\Services\MixpanelService;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use UserBundle\Entity\User;

class MixpanelListener
{
    /**
     * @var MixpanelService
     */
    protected $mixpanel;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var BalanceService
     */
    protected $balanceService;

    public function __construct(MixpanelService $mixpanel, TokenStorage $tokenStorage, BalanceService $balanceService)
    {
        $this->mixpanel = $mixpanel;
        $this->tokenStorage = $tokenStorage;
        $this->balanceService = $balanceService;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token instanceof TokenInterface ? $token->getUser() : null;

        if ($user instanceof User && $this->mixpanel->hasEvents()) {
            $this->mixpanel->identify($user, $this->balanceService->getBalanceForUser($user));
        }

        $this->mixpanel->dumpEventBag();
    }
}
