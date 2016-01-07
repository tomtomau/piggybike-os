<?php

namespace AdminBundle\Models;

/**
 * Class ProductSearch
 * @package AdminBundle\Models
 * @author Tom Newby <tom.newby@redeye.co>
 */
class ProductSearch
{
    /** @var string */
    protected $query;
    protected $query2;

    protected $isSearched = false;

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery2()
    {
        return $this->query2;
    }

    /**
     * @param mixed $query2
     * @return $this
     */
    public function setQuery2($query2)
    {
        $this->query2 = $query2;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSearched() : bool
    {
        return (bool) $this->isSearched;
    }

    /**
     * @param boolean $isSearched
     * @return $this
     */
    public function setIsSearched($isSearched)
    {
        $this->isSearched = $isSearched;

        return $this;
    }
}