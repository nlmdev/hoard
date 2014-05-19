<?php

namespace HoardPool\Test;

class RedisTest extends \Hoard\PoolTest
{

    protected function getPool()
    {
        $pool = \Hoard\CacheManager::getPool('test.redis');
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
        $this->assertEquals($this->getPool()->getName(), 'test.redis');
    }

}
