<?php

namespace AdminBundle\Controller;

use AdminBundle\Models\ProductSearch;
use Doctrine\Common\Collections\ArrayCollection;
use RewardBundle\Entity\Product;
use RewardBundle\Model\ProductCategory;
use RewardBundle\Repository\ProductRepository;
use RewardBundle\Services\ProductCategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProductController
 * @package AdminBundle\Controller
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request) {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->get('reward.product_repository');

        $productSearch = new ProductSearch();

        $searchForm = $this->createFormBuilder($productSearch)
            ->add('query', 'text', array('required' => true))
            ->add('query2', 'text', array('required' => false))
            ->add('search', 'submit', array('label' => "Search"))
            ->getForm();

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $query = $productSearch->getQuery();

            $productSearch->setIsSearched(true);
            if (null === $productSearch->getQuery2()) {
                $products = $productRepository->findByTitleContaining($productSearch->getQuery());
            } else {
                $products = $productRepository->findByTitleContaining($productSearch->getQuery(), $productSearch->getQuery2());
            }

        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('AdminBundle:Product:index.html.twig',
            array(
                'products' => $products,
                'product_search' => $productSearch,
                'search_form' => $searchForm->createView()
            )
        );
    }

    public function categoriesAction(Request $request) {
        /** @var ProductCategoryService $productCategoryService */
        $productCategoryService = $this->get('reward.product_category_service');

        $categories = $productCategoryService->getCategories();

        /** @var ProductRepository $productRepo */
        $productRepo = $this->get('reward.product_repository');

        // I don't really like the way I'm doing this

        $categoriesProducts = array();

        foreach ($categories as $key => $category) {
            $categoriesProducts[$key] = $productRepo->findBy(array(
                'category' => $key
            ));
        }

        return $this->render('AdminBundle:Product:categories.html.twig',
            array(
                'categories' => $categories,
                'cat_products' => $categoriesProducts
            )
        );
    }

    public function editAction(Request $request, $id) {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->get('reward.product_repository');

        /** @var ProductCategoryService $productCategoryService */
        $productCategoryService = $this->get('reward.product_category_service');

        $product = $productRepo->find($id);

        if (!$product instanceof Product) {
            throw new NotFoundHttpException();
        }

        $categories = new ArrayCollection($productCategoryService->getCategories());

        $formattedCategories = array();

        foreach ($categories as $key => $category) {
            /** @var ProductCategory $category */
            $formattedCategories[$category->getTitle()] = $key;
        }

        $form = $this->createFormBuilder($product)
            ->add('category', ChoiceType::class,
                array('choices' => $formattedCategories, 'choices_as_values' => true, 'required' => false)
            )
            ->add('save', 'submit', array())
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productRepo->save($product);

            return $this->redirectToRoute('admin.product.list');
        }

        return $this->render('AdminBundle:Product:edit.html.twig',
            array(
                'form' => $form->createView(),
                'product' => $product
            )
        );
    }
}