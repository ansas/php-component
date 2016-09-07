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
        return array(
            new Twig_SimpleFilter('ucfirst', 'ucfirst'),
            new Twig_SimpleFilter('gettype', 'gettype'),
            new Twig_SimpleFilter('strlen', 'strlen'),
            new Twig_SimpleFilter('count', 'count'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'phpfunctions';
    }
}
