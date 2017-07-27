<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

/**
 * Class Html
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class Html
{
    /**
     * Decode HTML from escaped version.
     *
     * @param string $html
     *
     * @return string
     */
    public static function decode($html)
    {
        return html_entity_decode($html);
    }

    /**
     * Encode HTML to escaped version.
     *
     * @param string $html
     *
     * @return string
     */
    public static function encode($html)
    {
        return htmlentities($html);
    }

    /**
     * Remove all attributes of all tags.
     *
     * @param string $html
     *
     * @return string
     */
    public static function stripAttributes($html)
    {
        return preg_replace('/<(\/?)\s*([a-z]+)(?:\s+[a-z]+(?:=[^>]*)?)*\s*(\/?)>/uis', '<$1$2$3>', $html);
    }

    /**
     * Remove tags.
     *
     * @param string       $html
     * @param string|array $allowable [optional] Tags to keep.
     *
     * @return string
     */
    public static function stripTags($html, $allowable = [])
    {
        if (!is_array($allowable)) {
            $allowable = preg_split("/, */", $allowable, -1, PREG_SPLIT_NO_EMPTY);
        }

        if ($allowable) {
            return strip_tags($html, '<' . implode('><', $allowable) . '>');
        }

        return strip_tags($html);
    }
}
