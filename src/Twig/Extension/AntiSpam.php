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
 * Class AntiSpam
 *
 * @package Ansas\Twig\Extension
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class AntiSpam extends Twig_Extension
{
    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('entities', [$this, '_encodeEntities'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns a string converted to html entities.
     *
     * @see http://goo.gl/LPhtJ
     *
     * @param  string $string Value to be encoded
     *
     * @return string
     */
    public function _encodeEntities($string)
    {
        $string = mb_convert_encoding($string, 'UTF-32', 'UTF-8');
        $t      = unpack("N*", $string);
        $t      = array_map(function ($n) {
            return "&#$n;";
        },
            $t);

        return implode("", $t);
    }
}
