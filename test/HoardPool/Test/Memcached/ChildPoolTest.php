<?php

namespace HoardPool\Test\Memcached;

class ChildPoolTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();

        // get pools
        $this->parentPool = \Hoard\CacheManager::getPool('test.memcached');
        $this->childPool = \Hoard\CacheManager::getPool('test.memcached.child_pool');

        try {
            // test for extension
            $this->parentPool->getItem('doesnt_exist');
        }
        catch(\Exception $e) {
            if($e->getMessage() == 'Memcached extension is not installed.') {
                $this->markTestSkipped();
            }
            throw $e;
        }

        // rather than getting blank pools, we want to ensure existing data
        // stays in there and we use non-colliding keys
        $this->key = md5(rand() . time());
    }

    /**
     * @medium
     */
    public function testChildPoolIsIsolated()
    {
        // store something in the child pool
        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc');

        // make sure we can retrive it from the child pool
        $item = $this->childPool->getItem($this->key);
        $this->assertTrue($item->isHit());
        $this->assertEquals('abc', $item->get());

        // make sure we can not retrive it from the parent pool
        $item = $this->parentPool->getItem($this->key);
        $this->assertFalse($item->isHit());
    }

    public function testParentPoolIsIsolated()
    {
        // store something in the parent pool
        $item = $this->parentPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc');

        // make sure we can retrive it from the parent pool
        $item = $this->parentPool->getItem($this->key);
        $this->assertTrue($item->isHit());
        $this->assertEquals('abc', $item->get());

        // make sure we can not retrive it from the child pool
        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
    }

    public function testClearingChildPoolDoesntEffectParentPool()
    {
        // store something in the parent pool
        $item = $this->parentPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc1');

        // store something in the child pool
        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc2');

        // clear the child pool
        $this->childPool->clear();

        // check the items
        $item = $this->parentPool->getItem($this->key);
        $this->assertTrue($item->isHit());
        $this->assertEquals('abc1', $item->get());

        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
    }

    public function testClearParentPoolClearsChildPool()
    {
        // store something in the parent pool
        $item = $this->parentPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc1');

        // store something in the child pool
        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
        $item->set('abc2');

        // clear the parent pool
        $this->parentPool->clear();

        // check the items
        $item = $this->parentPool->getItem($this->key);
        $this->assertFalse($item->isHit());

        $item = $this->childPool->getItem($this->key);
        $this->assertFalse($item->isHit());
    }

    public function testPoolName()
    {
        $this->assertEquals($this->parentPool->getName(), 'test.memcached');
        $this->assertEquals($this->childPool->getName(), 'test.memcached.child_pool');
    }

}

