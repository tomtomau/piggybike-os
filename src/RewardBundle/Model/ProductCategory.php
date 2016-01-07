<?php

namespace RewardBundle\Model;

/**
 * Class ProductCategory
 * @package RewardBundle\Model
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ProductCategory
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $title;

    /**
     * ProductCategory constructor.
     * @param string $url
     * @param string $title
     */
    public function __construct($url, $title)
    {
        $this->url = $url;
        $this->title = $title;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}