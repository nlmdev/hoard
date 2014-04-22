<?php

namespace Hoard;

abstract class PoolTest extends \PHPUnit_Framework_TestCase
{

    protected abstract function getPool();

    public abstract function testPoolName();

    protected function assertNoSuchItem(\Hoard\ItemInterface $item, $expectedKey)
    {
        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());
        $this->assertEquals($expectedKey, $item->getKey());
    }

    public function testInterfaceIsPsr()
    {
        $this->assertTrue(is_subclass_of('\Hoard\PoolInterface', '\Psr\Cache\PoolInterface'));
    }

    public function testGetNonExistentItem()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem');
        $this->assertNoSuchItem($item, 'nosuchitem');
    }

    /**
     * @dataProvider setAndGetItemProvider
     */
    public function testSetAndGetItem($key, $value)
    {
        $pool = $this->getPool();
        $item = $pool->getItem($key);
        $this->assertNoSuchItem($item, $key);
        $item->set($value);

        $again = $pool->getItem($key);
        $this->assertTrue($again->isHit());
        $this->assertEquals($value, $again->get());
        $this->assertEquals($key, $again->getKey());
    }

    /**
     * We must ensure that all values stored in cache come out as the same type and value.
     */
    public function setAndGetItemProvider()
    {
        return array(
            array('true', true),
            array('false', false),
            array('int', 123),
            array('double', 12.34),
            array('string', 'my string'),
            array('array', array(1, '2', 3.4)),
            array('object', new \DateTime()),
            array('null', null),
        );
    }

    public function testSetAlreadyExpiredItem()
    {
        $pool = $this->getPool();

        // set an item that it already expired
        $item = $pool->getItem('nosuchitem');
        $this->assertNoSuchItem($item, 'nosuchitem');
        $item->set('nothing', new \DateTime('1st January 2008'));

        // fetch the item back again
        $item = $pool->getItem('nosuchitem');
        $this->assertNoSuchItem($item, 'nosuchitem');
    }

    /**
     * @expectedException \Psr\Cache\InvalidArgumentException
     * @expectedExceptionMessage Invalid argument for TTL.
     */
    public function testInvalidTTLTypeThrowsException()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem');
        $item->set('abc', 'oops');
    }

    public function testSetWithIntegerTTL()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem1');
        $item->set('abc1', 150);

        // fetch the item back again
        $item = $pool->getItem('nosuchitem1');
        $this->assertSame('abc1', $item->get());
    }

    public function testSetWithExpiredDateTimeTTL()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem');
        $item->set('abc', new \DateTime('7th March 2010'));

        // fetch the item back again
        $item = $pool->getItem('nosuchitem');
        $this->assertNoSuchItem($item, 'nosuchitem');
    }

    public function testSetWithDateTimeTTL()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem3');
        $item->set('abc', new \DateTime('7th March 2020'));

        // fetch the item back again
        $item = $pool->getItem('nosuchitem3');
        $this->assertSame('abc', $item->get());
    }

    public function testSetWithNullTTL()
    {
        $pool = $this->getPool();
        $item = $pool->getItem('nosuchitem2');
        $item->set('abc', null);

        // fetch the item back again
        $item = $pool->getItem('nosuchitem2');
        $this->assertSame('abc', $item->get());
    }

    public function testDeleteItem()
    {
        $pool = $this->getPool();

        // save item
        $item = $pool->getItem('nosuchitem');
        $item->set('abc');

        // delete it
        $item->delete();

        // fetch the item back again
        $item = $pool->getItem('nosuchitem');
        $this->assertFalse($item->isHit());
    }

}

