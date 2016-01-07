<?php

namespace AppBundle\Twig;

use AppBundle\Services\CurrencyService;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('currency_symbol', array($this, 'currencySymbol')),
        );
    }

    public function currencySymbol($currencyString)
    {
        return CurrencyService::getCurrencySymbol($currencyString);
    }

    public function getName()
    {
        return 'app_extension';
    }
}
