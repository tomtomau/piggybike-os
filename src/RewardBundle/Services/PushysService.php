<?php

namespace RewardBundle\Services;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use RewardBundle\Entity\Product;

class PushysService {

    /**
     * @var Client
     */
    protected $commissionFactory;

    /**
     * @var string
     */
    protected $commissionFactoryURL;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(Client $commissionFactory,
                                string $commissionFactoryURL,
                                EntityManager $entityManager) {
        $this->commissionFactory = $commissionFactory;
        $this->commissionFactoryURL = $commissionFactoryURL;
        $this->entityManager = $entityManager;
    }

    public function syncProducts() {
        $response = $this->commissionFactory->get(sprintf('%s/', $this->commissionFactoryURL));

        $responseJson = json_decode($response->getBody());

        $batchSize = 100;
        $i = 0;

        foreach ($responseJson as $product) {
            $price = $product->Price;
            $priceSale = $product->PriceSale;
            $name = $product->Name;
            $url = $product->Url;
            $image = $product->Image400;

            if (null !== $product->SKU) {
                $existingProduct = $this->entityManager->find('RewardBundle:Product', $product->SKU);

                if ($existingProduct instanceof Product) {
                    $existingProduct
                        ->setPrice($this->generatePrice($price))
                        ->setPriceSale($this->generatePrice($priceSale))
                        ->setName($name)
                        ->setUrl($url)
                        ->setImage($image)
                    ;
                } else {
                    $productEntity = new Product();
                    $productEntity
                        ->setId((int) $product->SKU)
                        ->setPrice($this->generatePrice($price))
                        ->setPriceSale($this->generatePrice($priceSale))
                        ->setName($name)
                        ->setUrl($url)
                        ->setImage($image)
                    ;

                    $this->entityManager->persist($productEntity);
                }
            }

            ++$i;

            if (0 === $i % $batchSize) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
    }

    protected function generatePrice(string $costString) {
        preg_match('/^\d+(?:.\d+)?/', str_ireplace(',', '', $costString), $matches);

        return (float) reset($matches);
    }
}