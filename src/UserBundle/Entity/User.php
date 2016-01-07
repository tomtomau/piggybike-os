<?php

namespace UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User.
 *
 * @author Tom Newby <tom.newby@redeye.co>
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=25, unique=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $state;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $accessToken;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ActivityBundle\Entity\Activity", mappedBy="user")
     */
    protected $activities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="RewardBundle\Entity\Reward", mappedBy="user")
     */
    protected $rewards;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $homeLat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $homeLng;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $workLat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $workLng;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $cost;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $seenConfirmation;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $currency;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $admin = false;

    /**
     * @var null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $growthOptin = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $haveAppendedHashtag = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $monthlyEmailOptOut = false;

    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @return float
     */
    public function getHomeLat()
    {
        return $this->homeLat;
    }

    /**
     * @param float $homeLat
     *
     * @return $this
     */
    public function setHomeLat($homeLat)
    {
        $this->homeLat = $homeLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getHomeLng()
    {
        return $this->homeLng;
    }

    /**
     * @param float $homeLng
     *
     * @return $this
     */
    public function setHomeLng($homeLng)
    {
        $this->homeLng = $homeLng;

        return $this;
    }

    /**
     * @return float
     */
    public function getWorkLat()
    {
        return $this->workLat;
    }

    /**
     * @param float $workLat
     *
     * @return $this
     */
    public function setWorkLat($workLat)
    {
        $this->workLat = $workLat;

        return $this;
    }

    /**
     * @return float
     */
    public function getWorkLng()
    {
        return $this->workLng;
    }

    /**
     * @param float $workLng
     *
     * @return $this
     */
    public function setWorkLng($workLng)
    {
        $this->workLng = $workLng;

        return $this;
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
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        if ($this->isAdmin()) {
            return array('ROLE_USER', 'ROLE_ADMIN');
        } else {
            return array('ROLE_USER');
        }
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.

        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return ArrayCollection
     */
    public function getRewards()
    {
        return $this->rewards;
    }

    /**
     * @param ArrayCollection $rewards
     *
     * @return $this
     */
    public function setRewards($rewards)
    {
        $this->rewards = $rewards;

        return $this;
    }

    /**
     * Can this user classify yet?
     *
     * @return bool
     */
    public function canClassify()
    {
        return $this->hasSetHome() && $this->hasSetWork();
    }

    /**
     * @return bool
     */
    public function hasSetHome()
    {
        return null !== $this->getHomeLat() && null !== $this->getHomeLng();
    }

    /**
     * @return bool
     */
    public function hasSetWork()
    {
        return null !== $this->getWorkLat() && null !== $this->getWorkLng();
    }

    /**
     * @return bool
     */
    public function hasSetCost()
    {
        return null !== $this->getCost();
    }

    /**
     * Has this user completed the setup?
     *
     * @return bool
     */
    public function isSetup()
    {
        return $this->canClassify() && $this->hasSetCost();
    }

    /**
     * @return \DateTime
     */
    public function hasSeenConfirmation()
    {
        return null !== $this->seenConfirmation;
    }

    /**
     * @param \DateTime $seenConfirmation
     *
     * @return $this
     */
    public function setSeenConfirmation($seenConfirmation)
    {
        $this->seenConfirmation = $seenConfirmation;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmationTime()
    {
        return $this->seenConfirmation;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     *
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMonthlyEmailOptOut()
    {
        return $this->monthlyEmailOptOut;
    }

    /**
     * @param bool $monthlyEmailOptOut
     *
     * @return $this
     */
    public function setMonthlyEmailOptOut($monthlyEmailOptOut)
    {
        $this->monthlyEmailOptOut = $monthlyEmailOptOut;

        return $this;
    }

    /**
     * @return null
     */
    public function getGrowthOptin()
    {
        return $this->growthOptin;
    }

    /**
     * @param null $growthOptin
     * @return $this
     */
    public function setGrowthOptin($growthOptin)
    {
        $this->growthOptin = $growthOptin;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHaveAppendedHashtag()
    {
        return $this->haveAppendedHashtag;
    }

    /**
     * @param boolean $haveAppendedHashtag
     * @return $this
     */
    public function setHaveAppendedHashtag($haveAppendedHashtag)
    {
        $this->haveAppendedHashtag = $haveAppendedHashtag;
        return $this;
    }
}
