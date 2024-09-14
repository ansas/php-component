<?php

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Net;

use PHPUnit\Framework\TestCase;

class IPv6Test extends TestCase
{
    public function testCreate()
    {
        $ip = new IPv6('::1');
        $this->assertInstanceOf(IPv6::class, $ip);
        $this->assertEquals('::1', $ip->getIp());
    }

    public function testIsValid()
    {
        $this->assertTrue(IPv6::create('ff01::101')->isValid());
        $this->assertTrue(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->isValid());

        $this->assertFalse(IPv6::create('1')->isValid());
        $this->assertFalse(IPv6::create('127.0.0.1')->isValid());
    }

    public function testGetIpLong()
    {
        $this->assertEquals('ff01:0000:0000:0000:0000:0000:0000:0101', IPv6::create('ff01::101')->getIpLong());
        $this->assertEquals('0000:0000:0000:0000:0000:0000:0000:0001', (new IPv6('::1'))->getIpLong());
    }

    public function testGetIpShort()
    {
        $this->assertEquals('ff01::101', IPv6::create('ff01:0000:0000:0000:0000:0000:0000:0101')->getIpShort());
        $this->assertEquals('::1', (new IPv6('0000:0000:0000:0000:0000:0000:0000:0001'))->getIpShort());
    }

    public function testGetPrefix()
    {
        $ip = IPv6::create('2a02:8206:325c:1c00:a085:ec9f:42e3:1d7f')->getPrefix(56);

        $this->assertEquals('2a02:8206:325c:1c00:0:0:0:0', $ip->getIp());
        $this->assertEquals('2a02:8206:325c:1c00:0000:0000:0000:0000', $ip->getIpLong());
        $this->assertEquals('2a02:8206:325c:1c00::', $ip->getIpShort());
    }

    public function testEquals()
    {
        $this->assertTrue(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->equals(new IPv6('::1')));
        $this->assertFalse(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->equals(new IPv6('::2')));
    }

    public function testEqualsPrefix()
    {
        $this->assertTrue(IPv6::create('2a02:8206:32ff:969c:a085:ec9f:42e3:1d7f')->equalsPrefix(new IPv6('2a02:8206:32ff:969c:9a9b:cbff:fec2:77d3'), 64));
    }
}
