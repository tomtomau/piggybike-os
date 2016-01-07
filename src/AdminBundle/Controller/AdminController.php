<?php

namespace AdminBundle\Controller;

use BCC\ResqueBundle\Resque;
use RewardBundle\Jobs\SyncPushysJob;
use StravaBundle\Services\AthleteService;
use StravaBundle\Services\ClientFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class AdminController extends Controller
{
    public function indexAction()
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->get('user_bundle.user_repository');

        $users = $userRepository->findAll();

        $setupUserCount = count(array_filter(array_map(function (User $user) { return $user->getConfirmationTime(); }, $users)));

        /** @var AthleteService $athleteService */
        $athleteService = $this->get('strava.athlete_service');

        /** @var ClientFactory $clientFactory */
        $clientFactory = $this->get('strava.client_factory');

        /** @var User $user */
        $user = $this->getUser();

        $client = $clientFactory->createClientFromAccessToken($user->getAccessToken());

        $userChartData = [];
        $userRegistrations = [];

        foreach ($users as $registeredUser) {
            /** @var User $registeredUser */
            if (!$registeredUser->hasSeenConfirmation()) {
                continue;
            }

            /** @var \DateTime $seenConfirmationDateTime */
            $seenConfirmationDateTime = $registeredUser->getConfirmationTime();

            $cloneDateTime = clone $seenConfirmationDateTime;

            // Reset the time
            $cloneDateTime->setTime(0, 0, 0);

            $key = $cloneDateTime->getTimestamp() * 1000;

            if (array_key_exists($key, $userRegistrations)) {
                ++$userRegistrations[$key];
            } else {
                $userRegistrations[$key] = 1;
            }
        }

        ksort($userRegistrations);
        $prevCount = 0;
        foreach ($userRegistrations as $date => $count) {
            $prevCount += $count;
            $userChartData[] = [$date, $prevCount];
        }

        return $this->render('AdminBundle:Admin:index.html.twig',
            array(
                'users' => $users,
                'setup_user_count' => $setupUserCount,
                'user_chart_data' => array(array('values' => $userChartData, 'key' => 'Users')),
            )
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function syncCFAction() {
        $job = new SyncPushysJob();

        /** @var Resque $resque */
        $resque = $this->get('bcc_resque.resque');

        $resque->enqueue($job);

        return $this->redirectToRoute('admin.index');
    }
}
