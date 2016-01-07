<?php

namespace RewardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use RewardBundle\Entity\Product;
use RewardBundle\Entity\Reward;
use UserBundle\Entity\User;

/**
 * Class ProductRepository
 * @package RewardBundle\Repository
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ProductRepository extends EntityRepository
{
    /**
     * @param string $contains
     * @param string $contains2
     * @return array
     */
    public function findByTitleContaining(string $contains, string $contains2 = null)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.name LIKE :contains')
            ->setParameter('contains', '%'.$contains.'%');

        if (null !== $contains2) {
            $qb->andWhere('product.name LIKE :contains2')
            ->setParameter('contains2', '%'.$contains2.'%');
        }

        return $qb
            ->orderBy('product.priceSale', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @param array $categories
     * @return array
     */
    public function findByCategories(array $categories = array()) {
        $qb = $this->createQueryBuilder('p')
            ->where('p.category IN (:categories)')
            ->groupBy('p.category')
            ->setParameter('categories', $categories)
            ->getQuery()->getResult();

        return $qb;
    }

    public function save(Product $product)
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush($product);
    }
}
