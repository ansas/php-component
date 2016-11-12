<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Twig\Extension;

use NumberFormatter;
use Twig_Extensions_Extension_Intl;
use Twig_SimpleFilter;

/**
 * Class PhpFunctions
 *
 * @package Ansas\Twig\Extension
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Intl extends Twig_Extensions_Extension_Intl
{
    public function getFilters()
    {
        $filters   = parent::getFilters();
        $filters[] = new Twig_SimpleFilter('localizedcurrencysymbol', [$this, '_getCurrencySymbol']);

        return $filters;
    }

    function _getCurrencySymbol($currency, $locale = null)
    {
        $formatter = twig_get_number_formatter("{$locale}@currency={$currency}", 'currency');

        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }
}
