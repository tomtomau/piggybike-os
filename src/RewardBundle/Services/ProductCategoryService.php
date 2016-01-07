<?php

namespace RewardBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use RewardBundle\Model\ProductCategory;
use RewardBundle\Repository\ProductRepository;

class ProductCategoryService {

    const TUBES = "tubes";
    const TYRES = "tyres";
    const LIGHTS = "lights";
    const PANNIERS = "panniers";

    /**
     * @var array
     */
    protected $categories;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * ProductCategoryService constructor.
     * @param string $baseRedirectURL
     */
    public function __construct(string $baseRedirectURL, ProductRepository $productRepository)
    {
        $this->categories =
            array(
                self::TUBES => new ProductCategory($baseRedirectURL . '/bike-tubes-and-tyres/bike-tubes/700c-bike-tubes.html?dir=asc&order=price', 'tubes'),
                self::TYRES => new ProductCategory($baseRedirectURL . '/bike-tubes-and-tyres/bike-tyres/700c-bike-tyres.html?dir=asc&order=price', 'tyres'),
                self::LIGHTS => new ProductCategory($baseRedirectURL . '/bike-accessories/bicycle-lights.html?dir=asc&order=price&price=10-', 'lights'),
                self::PANNIERS => new ProductCategory($baseRedirectURL . '/bike-accessories/bicycle-bags/pannier-bags.html?dir=asc&order=price', 'panniers'),
            );

        $this->productRepository = $productRepository;
    }

    public function getCategories() {
        return $this->categories;
    }

    public function getCategoriesWithProducts() {
        $categories = $this->getCategories();

        $catKeys = array_keys($categories);

        return $this->productRepository->findByCategories($catKeys);
    }
}