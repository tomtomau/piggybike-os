<?php

namespace RewardBundle\Entity;

use Symfony\Component\Validator\Constraints\DateTime;
use UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Reward.
 *
 * @author Tom Newby <tom.newby@redeye.co>
 *
 * @ORM\Table(name="rewards")
 * @ORM\Entity(repositoryClass="RewardBundle\Repository\RewardRepository")
 */
class Reward
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=2, precision=6)
     */
    protected $cost;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="rewards")
     */
    protected $user;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

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
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return array
     */
    public function serialize() : array {
        return [
            'title' => $this->getTitle(),
            'cost' => $this->getCost(),
            'date' => $this->getDate()->getTimestamp(),
            'user' => $this->getUser()->getId()
        ];
    }
}
