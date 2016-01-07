<?php

namespace UserBundle\Repository;

use ActivityBundle\Entity\Activity;
use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param $username
     *
     * @return User
     */
    public function createUser($username, $accessToken)
    {
        $em = $this->getEntityManager();

        $user = new User($username);
        $user->setAccessToken($accessToken);

        $em->persist($user);
        $em->flush($user);

        return $user;
    }

    /**
     * @param $username
     *
     * @return null|User
     */
    public function findByUsername($username)
    {
        return $this->findOneBy([
                'username' => $username,
            ]
        );
    }

    public function updateUser(User $user)
    {
        $em = $this->getEntityManager();

        $uow = $em->getUnitOfWork();

        if ($uow->isInIdentityMap($user)) {
            $em->flush();
        }
    }

    /**
     * @return array
     */
    public function findUsersForMonthlyEmail(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.activities', 'a')
            ->where('u.seenConfirmation is not null')
            ->andWhere('u.monthlyEmailOptOut = false')
            ->andWhere('a.classification in (:classifications)')
            ->andWhere('a.startDate > :startDate')
            ->andWhere('a.startDate < :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('classifications', array(Activity::CLASSIFY_COMMUTE_OUT, Activity::CLASSIFY_COMMUTE_IN))
            ->groupBy('u.id')
            ->having('count(a) > 0')
            ->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findUserForRewardsIntroEmail()
    {
        return $this->createQueryBuilder('u')
            ->where('u.seenConfirmation is not null')
            ->andWhere('u.country = :country')
            ->setParameter('country', 'Australia')
            ->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param $accessToken
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function updateAccessToken(User $user, $accessToken)
    {
        return $this->createQueryBuilder('u')
            ->update()
            ->set('u.accessToken', $accessToken)
            ->where('u = :user')
            ->setParameter('user', $user);
    }

    /**
     * @return mixed
     */
    public function getCountUser()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()->getSingleScalarResult();
    }
}
