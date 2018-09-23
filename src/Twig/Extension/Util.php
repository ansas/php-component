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

use InvalidArgumentException;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Class PhpFunctions
 *
 * @package Ansas\Twig\Extension
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Util extends Twig_Extension
{
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('util', [$this, '_staticCall']),
            new Twig_SimpleFunction('util_*', [$this, '_staticCall']),
        ];
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    function _staticCall()
    {
        $args = func_get_args();
        if (!$args) {
            throw new InvalidArgumentException("Util class name missing");
        }

        $class = array_shift($args);
        $class = '\\Ansas\\Util\\' . ucfirst($class);
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Util class {$class} does not exist");
        }

        $method = array_shift($args);
        if (!method_exists($class, $method)) {
            throw new InvalidArgumentException("Method {$method} for util class {$class} does not exist");
        }

        return @call_user_func_array([$class, $method], $args);
    }
}
