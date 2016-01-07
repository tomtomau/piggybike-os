<?php

namespace MixpanelBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Mixpanel;
use MixpanelBundle\Mixpanel\Event;
use UserBundle\Entity\User;

class MixpanelService
{
    /**
     * @var Mixpanel
     */
    protected $mixpanel;

    /**
     * @var ArrayCollection
     */
    protected $eventBag;

    public function __construct($mixpanelToken)
    {
        $this->mixpanel = Mixpanel::getInstance($mixpanelToken);
        $this->eventBag = new ArrayCollection();
    }

    public function getMixpanel()
    {
        return $this->mixpanel;
    }

    public function addEvent(Event $event)
    {
        $this->eventBag->add($event);

        return $this;
    }

    public function identify(User $user, $balance = null)
    {
        $this->mixpanel->identify($user->getId());

        $peopleProperties = self::getPeoplePropertiesForUser($user);

        if (null !== $balance) {
            $peopleProperties['Balance'] = $balance;
        }

        $this->mixpanel->people->set($user->getId(), $peopleProperties);
    }

    public static function getPeoplePropertiesForUser(User $user)
    {
        return array(
            '$first_name' => $user->getFirstName(),
            '$email' => $user->getEmail(),
            '$city' => $user->getCity(),
            '$state' => $user->getState(),
            '$country' => $user->getCountry(),
        );
    }

    /**
     * @param User  $user
     * @param array $properties
     */
    public function setOnce(User $user, array $properties = array())
    {
        $this->mixpanel->people->setOnce($user->getId(), $properties);
    }

    public function dumpEventBag()
    {
        foreach ($this->eventBag as $event) {
            if ($event instanceof Event) {
                $this->mixpanel->track($event->getEventName(), $event->getProperties());
            }
        }

        $this->eventBag->clear();
    }

    public function hasEvents()
    {
        return $this->eventBag->count() > 0;
    }
}
