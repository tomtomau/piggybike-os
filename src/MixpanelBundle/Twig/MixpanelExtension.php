<?php

namespace MixpanelBundle\Twig;

use MixpanelBundle\Services\MixpanelService;
use UserBundle\Entity\User;

class MixpanelExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('people_properties', array($this, 'getPeopleProperties')),
        );
    }

    public function getPeopleProperties(User $user)
    {
        return MixpanelService::getPeoplePropertiesForUser($user);
    }

    public function getName()
    {
        return 'mixpanel_extension';
    }
}
