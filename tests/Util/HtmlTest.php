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

    /** @noinspection SpellCheckingInspection */
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
            Html::stripAttributes('<b title>abc</b>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('< b title="abc" z-index=2 title >abc</ b >')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripAttributes('<' . "\n\n" . 'b title="abc"' . "\n\n" . 'z-index=2 ' . "\n\n" . '>abc</b>')
        );
        $this->assertEquals(
            'There are »>bla< objects«. And there are »>blabla< bla objects« out there.',
            Html::stripAttributes('There are »>bla< objects«. And there are »>blabla< bla objects« out there.')
        );
    }

    public function testStripEmptyTags()
    {
        $this->assertEquals(
            'äöü',
            Html::stripEmptyTags('<p></p>äöü')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::stripEmptyTags('<p></p><b>abc<i> </i></b>')
        );
        $this->assertEquals(
            ' <b>abc</b>',
            Html::stripEmptyTags('<p title="abc"><i>' . "\n\t\r\n" . '</i></p> <b>abc</b>')
        );
    }

    /** @noinspection HtmlRequiredLangAttribute */
    public function testFix()
    {
        $this->assertEquals(
            'äöü',
            Html::fix('äöü')
        );
        $this->assertEquals(
            '<p>äöü</p>',
            Html::fix('<p>äöü</p></p>')
        );
        $this->assertEquals(
            '<p>abc</p>',
            Html::fix('<p>abc</p>')
        );
        $this->assertEquals(
            '<b>abc</b>',
            Html::fix('</p><b>abc</b>')
        );
        $this->assertEquals(
            '<body>abc</body>',
            Html::fix('<html lang="de"><body>abc</body></html>')
        );
        $this->assertEquals(
            '<body>abc</body>',
            Html::fix('<html><body>abc</u></body></b></html>')
        );
        $this->assertEquals(
            '<body>abc</body>',
            Html::fix('<html><body>abc</u></body></b></html>')
        );
        $this->assertEquals(
            '<b><b>abc</b></b>',
            Html::fix('<b><b>abc</b>')
        );
    }

    public function testIsValid()
    {
        $this->assertSame(
            true,
            Html::isValid(null)
        );
        $this->assertSame(
            true,
            Html::isValid('')
        );
        $this->assertEquals(
            false,
            Html::isValid('<p>äöü</p></p>')
        );
        $this->assertEquals(
            false,
            Html::isValid('</p><p>äöü</p>')
        );
        $this->assertEquals(
            true,
            Html::isValid('<p>abc</p> def')
        );
        $this->assertEquals(
            false,
            Html::isValid('<p>abc <p>abc</p><br><p>abc</p><br /><img> abc ')
        );
        $this->assertEquals(
            true,
            Html::isValid('<!DOCTYPE html><html><body><p>abc</p><br><p>abc</p><br /><img></body></html>')
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

    public function testCombination()
    {
        $actual   = '<ol><li>bla<br /><a href="">bli</li></ol></div>';
        $expected = "<ol><li>bla<br>bli</li></ol>";

        $actual = Html::stripTags($actual, ['strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'p', 'div', 'br']);
        $actual = Html::stripEmptyTags($actual);
        $actual = Html::stripAttributes($actual);
        $actual = Html::fix($actual);

        $this->assertEquals($expected, $actual);
    }
}
