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
use Twig_SimpleTest;

/**
 * Class ExtendedTests
 *
 * @package Ansas\Twig\Extension
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ExtendedTests extends Twig_Extension
{
    public function getTests()
    {
        $filters = [
            new Twig_SimpleTest('instanceof', [$this, '_instanceof']),
        ];

        return $filters;
    }

    function _instanceof($var, $instance)
    {
        return $var instanceof $instance;
    }
}
