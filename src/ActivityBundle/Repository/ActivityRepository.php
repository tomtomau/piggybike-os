<?php

namespace ActivityBundle\Repository;

use ActivityBundle\Entity\Activity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

class ActivityRepository extends EntityRepository
{
    public function findActivitiesForFeed(User $user)
    {
        return $this->findBy(
            array('user' => $user),
            array('startDate' => 'desc')
        );
    }

    /**
     * @param User $user
     * @param int  $page
     * @param int  $perPage
     *
     * @return array
     */
    public function findPaginatedActivitiesForFeed(User $user, $page, $perPage = 10)
    {
        $offset = (((int) $page) - 1) * $perPage;

        return $this->findBy(
            array('user' => $user),
            array('startDate' => 'desc'),
            $perPage, $offset
        );
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function getActivityCount(User $user)
    {
        return (int) $this->createQueryBuilder('activity')
            ->select('count(activity.id)')
            ->where('activity.user = :user')
            ->setParameter('user', $user)
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $resourceId
     *
     * @return null|Activity
     */
    public function findByResourceId($resourceId)
    {
        return $this->findOneBy(
            array('resourceId' => $resourceId)
        );
    }

    /**
     * @param $resourceId
     *
     * @return bool
     */
    public function hasResourceWithId($resourceId)
    {
        return null !== $this->findByResourceId($resourceId);
    }

    public function save(Activity $activity)
    {
        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush($activity);
    }

    public function saveAll(array $activities)
    {
        foreach ($activities as $activity) {
            $this->getEntityManager()->persist($activity);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return ArrayCollection
     */
    public function findActivitiesToBeClassified()
    {
        return new ArrayCollection(
            $this->getToBeClassifiedQuery()
                ->getQuery()->getResult()
        )
        ;
    }
    /**
     * @return ArrayCollection
     */
    public function findActivitiesToBeClassifiedForUser(User $user)
    {
        return new ArrayCollection(
            $this->getToBeClassifiedQuery()
                ->andWhere('a.user = :user')
                ->setParameter('user', $user)
                ->getQuery()->getResult()
        )
        ;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getToBeClassifiedQuery()
    {
        return $this->createQueryBuilder('a')
                ->select('a')
                ->leftJoin('a.user', 'u')
                ->andWhere('u.homeLat is not null')
                ->andWhere('u.homeLng is not null')
                ->andWhere('u.workLat is not null')
                ->andWhere('u.workLng is not null')
                ->andWhere('u.cost is not null')
                ->andWhere('a.classifiedAt is null')
        ;
    }

    /**
     * @param User $user
     * @param $activityId
     *
     * @return Activity|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUsersActivityById(User $user, $activityId)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.user = :user')
            ->andWhere('a.id = :activityId')
            ->setParameter('user', $user)
            ->setParameter('activityId', $activityId)
            ->getQuery()->getOneOrNullResult();
    }

    public function getSavingsQuery(User $user)
    {
        return $this->createQueryBuilder('a')
            ->select('SUM(a.value)')
            ->where('a.user = :user')
            ->setParameter('user', $user);
    }

    public function getSavingsForUser(User $user, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        $query = $this->getSavingsQuery($user);

        if (null !== $startDate) {
            $query->andWhere('a.startDate > :start_date')
                ->setParameter('start_date', $startDate);
        }

        if (null !== $endDate) {
            $query->andWhere('a.startDate < :end_date')
                ->setParameter('end_date', $endDate);
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getSuggestedLocations(User $user)
    {
        $starts = $this->createQueryBuilder('a')
            ->select('a.startLat as lat')
            ->addSelect('a.startLng as lng')
            ->addSelect('count(a.id) as location_count')
            ->where('a.user = :user')
            ->groupBy('a.startLat')
            ->addGroupBy('a.startLng')
            ->orderBy('location_count', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        $ends = $this->createQueryBuilder('a')
            ->select('a.endLat as lat')
            ->addSelect('a.endLng as lng')
            ->addSelect('count(a.id) as location_count')
            ->where('a.user = :user')
            ->groupBy('a.endLat')
            ->addGroupBy('a.endLng')
            ->orderBy('location_count', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        $joined = array_merge($starts, $ends);

        $reduced = array_reduce($joined, function (ArrayCollection $carry, array $item) {
            $joinedKey = sprintf('%s,%s', $item['lat'], $item['lng']);
            if ($carry->containsKey($joinedKey)) {
                $value = $carry->get($joinedKey);

                $value['location_count'] += $item['location_count'];
                $carry->set($joinedKey, $value);
            } else {
                $carry->set($joinedKey, $item);
            }

            return $carry;
        }, new ArrayCollection())->toArray();

        usort($reduced, function (array $item1, array $item2) {
            return $item2['location_count'] - $item1['location_count'];
        });

        return $reduced;
    }

    /**
     * @param User      $user
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return mixed
     */
    public function getCommutesBetweenDates(User $user, \DateTime $startDate, \DateTime $endDate)
    {
        return $this
            ->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.user = :user')
            ->andWhere('a.startDate > :start_date')
            ->andWhere('a.startDate < :end_date')
            ->andWhere('a.classification in (:permittedClassifications)')
            ->setParameter('user', $user)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('permittedClassifications', array(
                Activity::CLASSIFY_COMMUTE_IN, Activity::CLASSIFY_COMMUTE_OUT,
            ))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMostRecentCommute(User $user) {
        return $this
            ->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.classification in (:permittedClassifications)')
            ->orderBy('a.startDate', 'desc')
            ->setParameter('user', $user)
            ->setParameter('permittedClassifications', array(
                Activity::CLASSIFY_COMMUTE_IN, Activity::CLASSIFY_COMMUTE_OUT,
            ))
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
