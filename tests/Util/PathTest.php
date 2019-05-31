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
class PathTest extends TestCase
{
    public function testFromCamelCase()
    {
        $this->assertEquals(
            '',
            Path::fromCamelCase('')
        );

        $this->assertEquals(
            'abc',
            Path::fromCamelCase('abc')
        );

        $this->assertEquals(
            'go/to/path',
            Path::fromCamelCase('goToPath')
        );

        $this->assertEquals(
            '/go/to/path',
            Path::fromCamelCase('GoToPath')
        );

        $this->assertEquals(
            'a/b/c',
            Path::fromCamelCase('aBC')
        );

        $this->assertEquals(
            '/a/b/c',
            Path::fromCamelCase('ABC')
        );
    }

    public function testToCamelCase()
    {
        $this->assertEquals(
            '',
            Path::toCamelCase('')
        );

        $this->assertEquals(
            'abc',
            Path::toCamelCase('abc')
        );

        $this->assertEquals(
            'goToPath',
            Path::toCamelCase('go/to/path')
        );

        $this->assertEquals(
            'GoToPath',
            Path::toCamelCase('/go/to/path')
        );

        $this->assertEquals(
            'aBC',
            Path::toCamelCase('a/b/c')
        );

        $this->assertEquals(
            'ABC',
            Path::toCamelCase('/a/b/c')
        );
    }
}
