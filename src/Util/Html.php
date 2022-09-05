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
     * Uses HTML 5 version including &apos; to ' conversion
     *
     * @param string $html
     *
     * @return string
     */
    public static function decode($html)
    {
        return html_entity_decode($html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
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
     * Remove empty tags.
     *
     * @param string $html
     *
     * @return string
     */
    public static function stripEmptyTags($html)
    {
        return preg_replace('/<([^<\/>]*)([^<\/>]*)>([\s]*?|(?R))*<\/\1>/imsU', '', $html);
    }

    /**
     * Fix HTML errors (tags not opening or closing correctly).
     *
     * @param string $html
     *
     * @return string
     */
    public static function fix($html)
    {
        // Add dummy tag to prevent DOMDocument from adding unwanted tags
        $html = '<dummy>' . $html . '</dummy>';

        // Add XML encoding to prevent DOMDocument from saving special chars as HTML entities
        $html = '<?xml encoding="utf-8" ?>' . $html;

        // Load HTML into document ignoring errors (sanitizes here) without adding html/body tags and doctype
        $doc = new \DOMDocument();
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

        // Save sanitized HTML and remove dummy tags again
        $html = $doc->saveHTML($doc->documentElement);
        $html = preg_replace('/^.*<dummy>|<\/dummy>.*$/', '', $html);

        return $html;
    }

    public static function isValid(?string $html): bool
    {
        // Check for tags
        if (null === $html || false === strpos($html, '<')) {
            return true;
        }

        // Get all tags in html string
        preg_match_all(
            "/<(?<closing>\/?)\s*(?<tag>[a-z]+)[^<>]*?(?<void>\/?)>/uis",
            strtolower($html),
            $matches,
            PREG_SET_ORDER
        );

        // Set of all void elements in HTML
        // @see https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html
        $void = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

        // Validate if HTML is nested correctly
        $stack = [];
        foreach ($matches as $match) {
            if (!$match['closing']) {
                if (!$match['void'] && !in_array($match['tag'], $void)) {
                    $stack[] = $match['tag'];
                }
            } else {
                $last = array_pop($stack);
                if (null === $last || $match['tag'] != $last) {
                    return false;
                }
            }
        }

        return !$stack;
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
