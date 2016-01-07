<?php

namespace RewardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RewardBundle\Entity\Reward;
use UserBundle\Entity\User;

class RewardRepository extends EntityRepository
{
    public function findRewardsForUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->orderBy('r.date', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getExpensesForUser(User $user)
    {
        return $this->createQueryBuilder('r')
            ->select('SUM(r.cost)')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function save(Reward $reward)
    {
        $this->getEntityManager()->persist($reward);
        $this->getEntityManager()->flush($reward);
    }

    public function remove(Reward $reward)
    {
        $this->getEntityManager()->remove($reward);
        $this->getEntityManager()->flush($reward);
    }
}
