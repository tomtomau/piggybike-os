<?php

namespace MixpanelBundle\Mixpanel;

class Event
{
    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var array
     */
    protected $properties;

    public function __construct($eventName, $properties = array())
    {
        $this->eventName = (string) $eventName;
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
}
