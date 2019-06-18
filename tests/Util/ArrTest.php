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
 * Class PathTest
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ArrTest extends TestCase
{
    public function testPath()
    {
        $data = ['foo' => ['bar' => 1]];

        $this->assertEquals(
            '1',
            Arr::path($data, 'foo.bar')
        );

        $this->assertEquals(
            '1',
            Arr::path($data, ['foo', 'bar'])
        );

        $this->assertEquals(
            ['bar' => 1],
            Arr::path($data, 'foo')
        );
    }

    public function testSetPath()
    {
        $data = ['foo' => ['bar' => 1]];

        $this->assertEquals(
            ['foo' => ['bar' => 2]],
            Arr::setPath($data, 'foo.bar', 2)
        );

        $this->assertEquals(
            ['foo' => 2],
            Arr::setPath($data, 'foo', 2)
        );

        $this->assertEquals(
            ['foo' => ['bar' => ['new' => 2]]],
            Arr::setPath($data, 'foo.bar.new', 2)
        );

        $this->assertEquals(
            ['foo' => ['bar' => 1, 'new' => 2]],
            Arr::setPath($data, 'foo.new', 2)
        );
    }
}
