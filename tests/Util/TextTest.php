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
 * Class TextTest
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class TextTest extends TestCase
{
    public function testStripLinks()
    {
        $this->assertEquals(
            '',
            Text::stripLinks('')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('http://test.de')
        );

        $this->assertEquals(
            '[...]',
            Text::stripLinks('//test.de', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('www.test.de')
        );

        $this->assertEquals(
            ' ',
            Text::stripLinks(' www.test.de/sub/?lang=en.')
        );

        $this->assertEquals(
            'Der Link: ',
            Text::stripLinks('Der Link: www.test.de/test/test.htm?test=1&test2=2')
        );

        $this->assertEquals(
            'First [...] LAST',
            Text::stripLinks('First https://test.de?test LAST', '[...]')
        );

        $this->assertEquals(
            '<b>[...]</b>',
            Text::stripLinks('<b>www.test.de</b>', '[...]')
        );
    }
}
