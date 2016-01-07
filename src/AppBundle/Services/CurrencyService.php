<?php

namespace AppBundle\Services;

use Symfony\Component\Intl\Intl;

class CurrencyService
{
    /**
     * @param null $currencyString
     *
     * @return null|string
     */
    public static function getCurrencySymbol($currencyString = null)
    {
        $dollarAlias = array('NZD', 'AUD');

        if (in_array($currencyString, $dollarAlias)) {
            $currencyString = 'USD';
        }

        $symbol = Intl::getCurrencyBundle()->getCurrencySymbol($currencyString);

        return null === $symbol ? '$' : $symbol;
    }
}
