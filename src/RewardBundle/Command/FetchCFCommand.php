<?php

namespace RewardBundle\Command;

use Doctrine\ORM\EntityManager;
use RewardBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FetchCFCommand
 * - Fetch CommissionFactory feed - this should eventually be quite regular...
 * @package RewardBundle\Command
 * @author Tom Newby <tom.newby@redeye.co>
 */
class FetchCFCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('fetch:cf')
            ->setDescription('Fetch commission factory');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cfFactory = $this->getContainer()->get('guzzle.client.commission_factory');

        $url = $this->getContainer()->getParameter('commission_factory_url');
        $response = $cfFactory->get(sprintf('%s/', $url));

        $responseJson = json_decode($response->getBody());

        $first = reset($responseJson);

        //$output->writeln(sprintf("%s", print_r($first, true)));

        $batchSize = 100;
        $i = 0;

        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        foreach ($responseJson as $product) {

            $price = $product->Price;
            $priceSale = $product->PriceSale;
            $sku = $product->SKU;
            $name = $product->Name;
            $url = $product->Url;
            $image = $product->Image400;

            if (null === $product->SKU) {
                $output->writeln(sprintf("Cannot write (%s)", print_r($product, true)));
            } else {
                $existingProduct = $entityManager->find('RewardBundle:Product', $product->SKU);

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

                    $entityManager->persist($productEntity);
                }
            }

            ++$i;

            if (0 === $i % $batchSize) {
                $entityManager->flush();
                $output->writeln(sprintf("Saved %d", $i));
            }
        }

        $entityManager->flush();
        $output->writeln(sprintf("Finished %d", $i));
    }

    protected function generatePrice(string $costString) {
        preg_match('/^\d+(?:.\d+)?/', str_ireplace(',', '', $costString), $matches);

        return (float) reset($matches);
    }
}