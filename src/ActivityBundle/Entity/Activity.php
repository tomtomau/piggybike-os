<?php

namespace ActivityBundle\Entity;

use Symfony\Component\Validator\Constraints\DateTime;
use UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Activity.
 *
 * @author Tom Newby <tom.newby@redeye.co>
 *
 * @ORM\Table(name="activities")
 * @ORM\Entity(repositoryClass="ActivityBundle\Repository\ActivityRepository")
 */
class Activity
{
    const CLASSIFY_COMMUTE_NO = 1;
    const CLASSIFY_COMMUTE_IN = 2;
    const CLASSIFY_COMMUTE_OUT = 3;
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    protected $resourceId;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="activities")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $startDateLocal;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $polyline;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $startLat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $startLng;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $endLat;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $endLng;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $classifiedAt;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $classification;

    /**
     * @var float
     * @ORM\Column(type="decimal", scale=3, precision=9, nullable=true)
     */
    protected $value;

    /**
     * @var int
     * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=true)
     */
    protected $elapsedTime;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $manual = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     *
     * @return $this
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

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
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|string $startDate
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setStartDate($startDate)
    {
        if ($startDate instanceof \DateTime) {
            $this->startDate = $startDate;
        } elseif (is_string($startDate)) {
            // Try converting to a datetime object
            $this->startDate = new \DateTime($startDate);
        } else {
            throw new \Exception(sprintf('Format of %s passed to setStartDate value', gettype($startDate)));
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDateLocal()
    {
        return $this->startDateLocal;
    }

    /**
     * @param \DateTime|string $startDateLocal
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setStartDateLocal($startDateLocal)
    {
        if ($startDateLocal instanceof \DateTime) {
            $this->startDateLocal = $startDateLocal;
        } elseif (is_string($startDateLocal)) {
            // Try converting to a datetime object
            $this->startDateLocal = new \DateTime($startDateLocal);
        } else {
            throw new \Exception(sprintf('Format of %s passed to setStartDateLocal value', gettype($startDate)));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPolyline()
    {
        return $this->polyline;
    }

    /**
     * @param string $polyline
     *
     * @return $this
     */
    public function setPolyline($polyline)
    {
        $this->polyline = $polyline;

        return $this;
    }

    /**
     * @return float
     */
    public function getStartLat()
    {
        return $this->startLat;
    }

    /**
     * @param float $startLat
     *
     * @return $this
     */
    public function setStartLat($startLat)
    {
        $this->startLat = $startLat;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartLng()
    {
        return $this->startLng;
    }

    /**
     * @param mixed $startLng
     *
     * @return $this
     */
    public function setStartLng($startLng)
    {
        $this->startLng = $startLng;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndLng()
    {
        return $this->endLng;
    }

    /**
     * @param mixed $endLng
     *
     * @return $this
     */
    public function setEndLng($endLng)
    {
        $this->endLng = $endLng;

        return $this;
    }

    /**
     * @return float
     */
    public function getEndLat()
    {
        return $this->endLat;
    }

    /**
     * @param float $endLat
     *
     * @return $this
     */
    public function setEndLat($endLat)
    {
        $this->endLat = $endLat;

        return $this;
    }

    /**
     * @return int
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @param int $classification
     *
     * @return $this
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;

        // No Commute = No Value
        if (self::CLASSIFY_COMMUTE_NO === $classification) {
            $this->setValue(0);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getClassifiedAt()
    {
        return $this->classifiedAt;
    }

    /**
     * @param \DateTime $classifiedAt
     *
     * @return $this
     */
    public function setClassifiedAt($classifiedAt)
    {
        $this->classifiedAt = $classifiedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClassified()
    {
        return null !== $this->getClassifiedAt();
    }

    public function getClassificationAsString()
    {
        if (!$this->isClassified()) {
            return 'Unclassified';
        }

        $classification = $this->getClassification();

        if (self::CLASSIFY_COMMUTE_IN === $classification) {
            return 'To Work';
        }

        if (self::CLASSIFY_COMMUTE_OUT === $classification) {
            return 'To Home';
        }

        if (self::CLASSIFY_COMMUTE_NO === $classification) {
            return 'N/A';
        }

        return 'Unclassified';
    }

    public static function getClassificationOptions()
    {
        return array(
            'To Work' => self::CLASSIFY_COMMUTE_IN,
            'To Home' => self::CLASSIFY_COMMUTE_OUT,
            'No commute' => self::CLASSIFY_COMMUTE_NO,
        );
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    /**
     * @param int $elapsedTime
     *
     * @return $this
     */
    public function setElapsedTime($elapsedTime)
    {
        $this->elapsedTime = $elapsedTime;

        return $this;
    }

    public function getEndDate()
    {
        $endDate = clone $this->getStartDate();

        return $endDate->add(\DateInterval::createFromDateString(sprintf('%d seconds', $this->elapsedTime)));
    }

    public function getEndDateLocal()
    {
        $endDateLocal = clone $this->getStartDateLocal();

        return $endDateLocal->add(\DateInterval::createFromDateString(sprintf('%d seconds', $this->elapsedTime)));
    }

    /**
     * @return bool
     */
    public function isCommute()
    {
        $classification = $this->getClassification();

        return $classification === self::CLASSIFY_COMMUTE_IN || $classification === self::CLASSIFY_COMMUTE_OUT;
    }

    /**
     * @return bool
     */
    public function isManual()
    {
        return $this->manual;
    }

    /**
     * @param bool $manual
     *
     * @return $this
     */
    public function setManual($manual)
    {
        $this->manual = $manual;

        return $this;
    }
}
