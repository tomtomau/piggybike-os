<?php

namespace UserBundle\Security;

use BCC\ResqueBundle\Resque;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use MixpanelBundle\Services\MixpanelService;
use StravaBundle\Jobs\ActivityLoadJob;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var MixpanelService
     */
    protected $mixpanelService;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Resque
     */
    protected $resque;

    /**
     * @var Logger
     */
    protected $slackInfoLogger;

    public function __construct(UserRepository $userRepository, MixpanelService $mixpanelService, Session $session, Resque $resque, Logger $slackInfoLogger)
    {
        $this->userRepository = $userRepository;
        $this->mixpanelService = $mixpanelService;
        $this->session = $session;
        $this->resque = $resque;
        $this->slackInfoLogger = $slackInfoLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = $this->loadUserByUsername($response->getUsername());

            $this->updateAccessToken($user, $response->getAccessToken());
        } catch (UsernameNotFoundException $exception) {
            // Let's create this user
            $user = $this->userRepository->createUser($response->getUsername(), $response->getAccessToken());

            $format = 'Y/m/d H:i:s';

            $before = new \DateTime();

            // After NYE

            $now = new \DateTime();
            $year = $now->format('Y');

            $after = new \DateTime($year.'/01/01 00:00:01');

            $job = new ActivityLoadJob();
            $job->args = array(
                'user' => $user->getId(),
                'before_date' => $before->format($format),
                'after_date' => $after->format($format),
            );

            $this->resque->enqueue($job);

            // Set "just registered"
            $this->session->set('just_registered', true);

            $stravaUrl = sprintf('https://www.strava.com/athletes/%d', $user->getUsername());

            $userCount = $this->userRepository->getCountUser();

            $this->slackInfoLogger->addInfo(sprintf('New account for %s (%s) - total %d users ',
                $response->getEmail(),
                $stravaUrl,
                $userCount
            ));
        }

        if ($response instanceof PathUserResponse) {
            $this->updateUserFromOAuthResponse($user, $response);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'UserBundle\\Entity\\User';
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findByUsername($username);

        if (null === $user) {
            throw new UsernameNotFoundException(sprintf('Cannot find username of %s', $username));
        }

        return $user;
    }

    /**
     * @param User $user
     * @param $accessToken
     *
     * @return $this
     */
    public function updateAccessToken(User $user, $accessToken)
    {
        $this->userRepository->updateAccessToken($user, $accessToken);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    protected function updateUserFromOAuthResponse(User $user, PathUserResponse $response)
    {
        $stravaResponse = $response->getResponse();

        $user->setAccessToken($response->getAccessToken());

        $this->autoUpdateUserFromResponse($user, array(
            'firstname',
            'city',
            'state',
            'country',
            'email',
        ), $stravaResponse);

        $this->userRepository->updateUser($user);
    }

    /**
     * @param User  $user
     * @param array $keys
     * @param array $stravaResponse
     *
     * @throws \Exception
     */
    protected function autoUpdateUserFromResponse(User $user, array $keys = array(), array $stravaResponse = array())
    {
        foreach ($keys as $key) {
            $this->updateUserFieldFromResponse($user, $key, $stravaResponse);
        }
    }

    /**
     * @param User $user
     * @param $key
     * @param array $stravaResponse
     *
     * @throws \Exception
     */
    protected function updateUserFieldFromResponse(User $user, $key, array $stravaResponse = array())
    {
        if (array_key_exists($key, $stravaResponse) && null !== $stravaResponse[$key] && strlen($stravaResponse[$key])) {
            $setterMethod = sprintf('set%s', $key);

            if (method_exists($user, $setterMethod)) {
                // Invoke the setter method
                $user->$setterMethod($stravaResponse[$key]);
            } else {
                throw new \Exception(sprintf('No setter method for %s', $key));
            }
        }
    }
}
