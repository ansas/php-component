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
    public function testReplaceKey()
    {
        $this->assertSame(
            ['foo', 'bar'],
            Arr::replaceKey(['foo', 'bar'], 'baz', 'BAZ')
        );

        $this->assertSame(
            ['BAZ', 'baz' => ['bar' => 1]],
            Arr::replaceKey(['foo' => ['bar' => 1], 'BAZ'], 'foo', 'baz')
        );

        $this->assertSame(
            ['two' => 2, 'three' => 1],
            Arr::replaceKey(['one' => 1, 'two' => 2], 'one', 'three')
        );

        $this->assertSame(
            ['three' => 1, 'two' => 2],
            Arr::replaceKey(['one' => 1, 'two' => 2], 'one', 'three', true)
        );
    }

    public function testReplaceKeys()
    {
        $this->assertSame(
            ['TWO' => 2, 'ONE' => 1],
            Arr::replaceKeys(['one' => 1, 'two' => 2], ['two' => 'TWO', 'one' => 'ONE'], false)
        );

        $this->assertSame(
            ['ONE' => 1, 'TWO' => 2],
            Arr::replaceKeys(['one' => 1, 'two' => 2], ['two' => 'TWO', 'one' => 'ONE'], true)
        );
    }

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

    public function testTranspose()
    {
        $this->assertEquals(
            [
                0 => [
                    'id' => 'x',
                    'quantity' => 1,
                    'ref' => ['a1', 'b1'],
                ],
                1 => [
                    'id' => 'y',
                    'quantity' => 2,
                    'ref' => null,
                ],
                2 => [
                    'id' => 'z',
                    'quantity' => 3,
                    'ref' => ['a3', 'b3'],
                    'once' => true,
                ],
            ],
            Arr::transpose([
                'id' => [
                    'x',
                    'y',
                    'z'
                ],
                'quantity' => [
                    1,
                    2,
                    3
                ],
                'ref' => [
                    ['a1', 'b1'],
                    null,
                    ['a3', 'b3'],
                ],
                'once' => [
                    2 => true,
                ],
            ])
        );
    }
}
