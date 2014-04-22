<?php

namespace Hoard\Pool\Test;

class MemcachedTest extends \Hoard\PoolTest
{

    protected function getPool()
    {
        $pool = \Hoard\CacheManager::getPool('test.memcached');
        $pool->clear();
        return $pool;
    }

    public function testClearChangesPrefix()
    {
        $pool = $this->getPool();
        $prefix1 = $pool->getAdapter()->getPrefix();
        $pool->clear();
        $prefix2 = $pool->getAdapter()->getPrefix();
        $this->assertNotEquals($prefix1, $prefix2);
    }

    public function testPoolName()
    {
        $this->assertEquals($this->getPool()->getName(), 'test.memcached');
    }

}

