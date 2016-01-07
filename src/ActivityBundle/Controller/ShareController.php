<?php

namespace ActivityBundle\Controller;

use ActivityBundle\Services\BalanceService;
use AppBundle\Services\CurrencyService;
use MixpanelBundle\Mixpanel\Event;
use MixpanelBundle\Services\MixpanelService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UserBundle\Entity\User;

class ShareController extends Controller
{
    public function shareAction()
    {
        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');
        $mixpanel->addEvent(new Event('Share Page'));

        $user = $this->getUser();
        /* @var BalanceService $balance */
        $balanceService = $this->get('activity.balance');

        $balance = $balanceService->getBalanceForUser($user);

        $currency = $user->getCurrency();

        $currencySymbol = CurrencyService::getCurrencySymbol($currency);

        $stringResponse = $currencySymbol.number_format($balance, 2);

        $shareMessages = array(
            'fb' => "I've saved $stringResponse cycling to work this year! I tracked it with PiggyBike!",
            'twitter' => $shareMessage = "I've saved $stringResponse cycling to work this year! I tracked it with @PiggyBike",
        );

        return $this->render('ActivityBundle:Share:share.html.twig', array('balance' => $balance, 'messages' => $shareMessages));
    }

    public function shareMonthAction($year, $month)
    {
        if (!$this->validateYearMonth($year, $month)) {
            return $this->redirectToRoute('activity.share.share');
        }

        $year = (int) $year;
        $month = (int) $month;

        $startDate = new \DateTime(sprintf('%s-%s-01', $year, $month));

        // End of that month
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1M'));

        /** @var MixpanelService $mixpanel */
        $mixpanel = $this->get('mixpanel');
        $mixpanel->addEvent(new Event('Share Month Page'));

        $user = $this->getUser();
        /* @var BalanceService $balance */
        $balanceService = $this->get('activity.balance');

        $balance = $balanceService->getMonthlyBalanceForUser($user, $startDate, $endDate);

        $currency = $user->getCurrency();

        $currencySymbol = CurrencyService::getCurrencySymbol($currency);

        $stringResponse = $currencySymbol.number_format($balance, 2);

        $shareMessages = array(
            'fb' => "I saved $stringResponse cycling to work this month! I tracked it with PiggyBike!",
            'twitter' => $shareMessage = "I saved $stringResponse cycling to work this month! I tracked it with @PiggyBike!",
        );

        $twitterUrl = sprintf('https://twitter.com/intent/tweet?text=%s&url=%s',
            urlencode($shareMessages['twitter']),
            urlencode('http://piggy.bike')
        );

        return $this->render('ActivityBundle:Share:month.html.twig',
            array('balance' => $balance, 'messages' => $shareMessages, 'twitter_share_url' => $twitterUrl,
            'start_date' => $startDate, )
        );
    }

    protected function validateYearMonth($year, $month)
    {
        $startDate = new \DateTime("2016-01-01 00:00:00");

        // Date of most recent email
        $mostRecent = new \DateTime("last day of last month");

        $testingTime = new \DateTime();
        $testingTime->setDate($year, $month, 01);

        return $testingTime > $startDate && $testingTime < $mostRecent;
    }

    public function tweetAction()
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            /* @var BalanceService $balance */
            $balanceService = $this->get('activity.balance');

            $balance = $balanceService->getBalanceForUser($user);

            $currency = $user->getCurrency();

            $currencySymbol = CurrencyService::getCurrencySymbol($currency);

            $stringResponse = $currencySymbol.number_format($balance, 2);

            $url = sprintf('https://twitter.com/intent/tweet?text=%s&url=%s', urlencode("I've saved $stringResponse cycling to work this year! I tracked it with @PiggyBike"), urlencode('http://piggy.bike'));

            /** @var MixpanelService $mixpanel */
            $mixpanel = $this->get('mixpanel');

            $mixpanel->identify($user);
            $mixpanel->addEvent(new Event('Share', array('Platform' => 'Twitter')));

            return $this->redirect($url);
        }

        return $this->redirectToRoute('activity.activity.list');
    }

    public function widgetAction()
    {
    }
}
