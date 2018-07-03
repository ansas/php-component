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

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Class PhpFunctions
 *
 * @package Ansas\Twig\Extension
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class PhpFunctions extends Twig_Extension
{
    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('ucfirst', 'ucfirst'),
            new Twig_SimpleFilter('gettype', 'gettype'),
            new Twig_SimpleFilter('getclass', 'get_class'),
            new Twig_SimpleFilter('strlen', 'strlen'),
            new Twig_SimpleFilter('count', 'count'),
            new Twig_SimpleFilter('php_*', [$this, '_callPhpFunction']),
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('php_*', [$this, '_callPhpFunction']),
        ];
    }

    function _callPhpFunction()
    {
        $args = func_get_args();
        $func = array_shift($args);

        return @call_user_func_array($func, $args);
    }
}
