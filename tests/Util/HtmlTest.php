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

use PHPUnit\Framework\TestCase;

/**
 * Class HtmlTest
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class HtmlTest extends TestCase
{
    public function testEncode()
    {
        $this->assertEquals(
            '',
            Html::encode('')
        );
        $this->assertEquals(
            'abc',
            Html::encode('abc')
        );
        $this->assertEquals(
            '&lt;b&gt;abc&lt;/b&gt;',
            Html::encode('<b>abc</b>')
        );
    }

    public function testDecode()
    {
        $this->assertEquals(
            '',
            Html::decode('')
        );
        $this->assertEquals(
            'abc',
            Html::decode('abc')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::decode('&lt;b&gt;abc&lt;/b&gt;')
        );
    }

    public function testStripAttributes()
    {
        $this->assertEquals(
            'abc',
            Html::stripAttributes('abc')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('<b>abc</b>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('<b title="abc">abc</b>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('< b title="abc" z-index=2 >abc</b>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('<' . "\n\n" . 'b title="abc"' . "\n\n" . 'z-index=2 ' . "\n\n" . '>abc</b>')
        );
    }

    public function testStripTags()
    {
        $this->assertEquals(
            'abc',
            Html::stripTags('<p><b>abc</b></p>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripTags('<p><b>abc</b></p>', 'b')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripTags('<p title="abc"><b>abc</b></p>', ['b'])
        );
        $this->assertEquals(
            '<b title="abc">abc</b>',
            Html::stripTags('<p><b title="abc">abc</b></p>', ['b'])
        );
        $this->assertEquals(
            '<p><b>abc</b></p>',
            Html::stripTags('<p><b>abc</b></p>', 'b, p')
        );
        $this->assertEquals(
            '<p title="abc"><b>abc</b></p>',
            Html::stripTags('<p title="abc"><b>abc</b></p>', ['b', 'p'])
        );
        $this->assertEquals(
            '<b>abc' . "\n\n" . '</b>',
            Html::stripTags('<p><b>abc' . "\n\n" . '</b></p>', 'b')
        );
    }
}
