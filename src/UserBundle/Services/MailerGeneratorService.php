<?php

namespace UserBundle\Services;

use ActivityBundle\Repository\ActivityRepository;
use ActivityBundle\Services\BalanceService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use UserBundle\Entity\User;

/**
 * Class MailerGeneratorService
 *  - Handles getting templates ready for mailing.
 *
 * @author Tom Newby <tom.newby@redeye.co>
 */
class MailerGeneratorService
{
    /**
     * @var string
     */
    protected $mixpanelToken;

    /**
     * @var BalanceService
     */
    protected $balanceService;

    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var Router
     */
    protected $router;

    public function __construct($mixpanelToken,
                                BalanceService $balanceService,
                                ActivityRepository $activityRepository, Router $router)
    {
        $this->mixpanelToken = (string) $mixpanelToken;
        $this->balanceService = $balanceService;
        $this->activityRepository = $activityRepository;
        $this->router = $router;
    }

    /**
     * @param User      $user
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param \DateTime $lastStart
     *
     * @return array
     */
    public function getMonthlyEmail(User $user, \DateTime $startDate, \DateTime $endDate, \DateTime $lastStart = null)
    {
        $name = $user->getFirstName();

        $month = $startDate->format('F');

        $balance = $this->balanceService->getMonthlyBalanceForUser($user, $startDate, $endDate);
        $savingString = $this->balanceService->formatBalance($user, $balance);

        $commuteCount = $this->activityRepository->getCommutesBetweenDates($user, $startDate, $endDate);

        $blurb = sprintf("Solid work this month, %s - we've automatically detected %d rides to/from work this month - that means <span style=\"color: #333; font-weight: 600;\">cycling to work saved you %s in %s.</span>",
            $name,
            $commuteCount,
            $savingString,
            $month
        );

        if ($lastStart instanceof \DateTime) {
            $lastMonth = $this->balanceService->getMonthlyBalanceForUser($user, $lastStart, $startDate);

            if ($lastMonth <> 0) {
                $diff = $balance - $lastMonth;

                $direction = $diff > 0 ? 'up' : 'down';

                $absDiff = abs($diff);

                $diffString = $this->balanceService->formatBalance($user, $absDiff);

                $blurb .= sprintf(" - %s %s from last month.", $direction, $diffString);
            } else {
                $blurb .= ".";
            }
        }

        $shareUrl = $this->router->generate('activity.share.month', array(
            'year' => $startDate->format('Y'),
            'month' => $startDate->format('m'),
        ));

        $promoUrl = $this->router->generate('reward.promo.list');

        return [
            'subject' => sprintf("PiggyBike: you saved %s in %s %s", $savingString, $month, "\u{1F4B0}"),
            'preview' => sprintf("Hi %s, you're now %s better off, because you rode to work this month!", $name, $savingString),
            'month' => $month,
            'blurb' => $blurb,
            'shareUrl' => $shareUrl,
            'promoUrl' => $promoUrl,
            'pixel' => $this->generatePixelUrl($user, 'Monthly Email', array('Month' => (new \DateTime())->format('Ym'))),
        ];
    }

    public function getRewardsIntroEmail(User $user) : array {
        $subject = sprintf('PiggyBike Purchases %s + Free Shipping %s', "\u{1F4B0}", "\u{1F4E6}");

        $preview = 'You can now track purchases in PiggyBike and find awesome promos!';

        return [
            'subject' => $subject,
            'preview' => $preview,
            'pixel' => $this->generatePixelUrl($user, 'Rewards Email'),
        ];
    }

    /**
     * @param User   $user
     * @param string $campaign
     *
     * @return string
     */
    protected function generatePixelUrl(User $user, $campaign, array $properties = array())
    {
        $properties = array_merge(
            $properties,
            array(
                'token' => $this->mixpanelToken,
                'distinct_id' => $user->getId(),
                'Campaign' => (string) $campaign,
            )
        );

        $tracking = array(
            'event' => 'Pixel Render',
            'properties' => $properties,
        );

        $encodedTracking = base64_encode(json_encode($tracking));

        return sprintf('http://api.mixpanel.com/track/?data=%s&ip=1&img=1', $encodedTracking);
    }
}
