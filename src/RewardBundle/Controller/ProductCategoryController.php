<?php

namespace RewardBundle\Controller;

use RewardBundle\Entity\Product;
use RewardBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductCategoryController
 * @package RewardBundle\Controller
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ProductCategoryController extends Controller
{
    /**
     * @return Response|\Symfony\Component\HttpFoundation\Response
     */
    public function printRandomCategoryAdAction() {

        $productCatService = $this->get('reward.product_category_service');

        $catProducts = $productCatService->getCategoriesWithProducts();

        if (empty($catProducts)) {
            return new Response('');
        }

        shuffle($catProducts);

        $product = reset($catProducts);

        $categories = $productCatService->getCategories();

        $category = $categories[$product->getCategory()];

        return $this->render('RewardBundle:ProductCategory:_category_card.html.twig', array(
            'product' => $product,
            'category' => $category
        ));
    }
}
