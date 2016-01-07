<?php

namespace RewardBundle\Entity;

use Symfony\Component\Validator\Constraints\DateTime;
use UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product.
 *
 * @author Tom Newby <tom.newby@redeye.co>
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="RewardBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $price;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $priceSale;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $image;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $category;

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param int $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceSale()
    {
        return $this->priceSale;
    }

    /**
     * @param float $priceSale
     * @return $this
     */
    public function setPriceSale($priceSale)
    {
        $this->priceSale = $priceSale;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }
}
