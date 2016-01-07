<?php

namespace ActivityBundle\Controller;

use ActivityBundle\Services\BalanceService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\User;

class BalanceController extends Controller
{
    public function printBalanceAction()
    {
        $user = $this->getUser();
        $stringResponse = '';

        if ($user instanceof User) {
            /* @var BalanceService $balance */
            $balanceService = $this->get('activity.balance');

            $now = new \DateTime();
            $year = $now->format('Y');

            /** @var float $balance */
            $balance = $balanceService->getBalanceForUser($user, new \DateTime('2016-01-01 00:00:00'));

            $stringResponse = $balanceService->formatBalance($user, $balance);
        }

        return new Response($stringResponse);
    }
}
