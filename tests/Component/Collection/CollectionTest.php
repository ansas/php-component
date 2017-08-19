<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Collection;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 *
 * @package Ansas\Component\Collection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CollectionTest extends TestCase
{
    public function testCreate()
    {
        $collection = new Collection();
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertTrue($collection->isEmpty());

        return $collection;
    }

    /**
     * @depends testCreate
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    public function testEmpty(Collection $collection)
    {
        $this->assertTrue($collection->isEmpty());

        $collection->replace([]);
        $this->assertTrue($collection->isEmpty());

        return $collection;
    }

    /**
     * @depends clone testCreate
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    public function testSetScalar(Collection $collection)
    {
        // Set via method
        $collection->set('name', 'max');
        $this->assertEquals(1, $collection->count());
        $this->assertEquals('max', $collection->get('name'));

        // Set via object property
        $collection->name = "jane";
        $this->assertEquals(1, $collection->count());
        $this->assertEquals('jane', $collection->name);

        // Set via array key
        $collection['name'] = "john";
        $this->assertEquals(1, $collection->count());
        $this->assertEquals('john', $collection['name']);

        return $collection;
    }

    /**
     * @depends clone testCreate
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    public function testSetArray(Collection $collection)
    {
        // Set via method
        $collection->set('set1', []);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals([], $collection->get('set1'));

        // Set via object property
        $collection->set2 = [100];
        $this->assertEquals(2, $collection->count());
        $this->assertEquals([100], $collection->set2);

        // Set via array key
        $collection['set3'] = ['john', 'joe'];
        $this->assertEquals(3, $collection->count());
        $this->assertEquals(['john', 'joe'], $collection['set3']);

        return $collection;
    }
}
