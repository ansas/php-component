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
        $filters[] = new Twig_SimpleFilter('localizeddecimal', [$this, '_getDecimal']);

        return $filters;
    }

    function _getCurrencySymbol($currency, $locale = null)
    {
        $formatter = twig_get_number_formatter("{$locale}@currency={$currency}", 'currency');

        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }

    function _getDecimal($number, $style = 'decimal', $precision = 0, $locale = null)
    {
        $formatter = clone twig_get_number_formatter($locale, $style);
        $precision = (int) $precision;

        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $precision);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);

        return $formatter->format($number);
    }
}
