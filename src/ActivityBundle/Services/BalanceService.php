<?php

namespace ActivityBundle\Services;

use ActivityBundle\Repository\ActivityRepository;
use AppBundle\Services\CurrencyService;
use RewardBundle\Repository\RewardRepository;
use UserBundle\Entity\User;

class BalanceService
{
    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var RewardRepository
     */
    protected $rewardRepository;

    public function __construct(ActivityRepository $activityRepository, RewardRepository $rewardRepository)
    {
        $this->activityRepository = $activityRepository;
        $this->rewardRepository = $rewardRepository;
    }

    /**
     * @param User $user
     *
     * @param \DateTime $startDate
     * @return float
     */
    public function getBalanceForUser(User $user, \DateTime $startDate = null)
    {
        // @TODO: Cache this in Redis

        $balance = $this->activityRepository->getSavingsForUser($user, $startDate);

        $expenses = $this->rewardRepository->getExpensesForUser($user);

        return (float) $balance - $expenses;
    }

    public function getMonthlyBalanceForUser(User $user, \DateTime $startDate, \DateTime $endDate)
    {
        return (float) $this->activityRepository->getSavingsForUser($user, $startDate, $endDate);
    }

    /**
     * @param User  $user
     * @param float $balance
     *
     * @return string
     */
    public function formatBalance(User $user, $balance)
    {
        $currency = $user->getCurrency();

        $currencySymbol = CurrencyService::getCurrencySymbol($currency);

        return $currencySymbol.number_format($balance, 2);
    }
}
