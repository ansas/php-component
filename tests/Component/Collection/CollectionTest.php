<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace Ansas\Component\Collection;

use PHPUnit\Framework\TestCase;

enum MockStatus: string
{
    case Active = 'active';
}

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

    public function testPathAcceptsRealEnums(): void
    {
        $collection = new class([
            'active' => [
                'admin' => 'Erlaubt'
            ]
        ]) extends Collection {
            protected function normalizeKey(mixed $key): mixed
            {
                return strtolower($key instanceof MockStatus ? $key->value : $key);
            }
        };

        $this->assertEquals('Erlaubt', $collection->path([MockStatus::Active, 'Admin']));
    }
}
