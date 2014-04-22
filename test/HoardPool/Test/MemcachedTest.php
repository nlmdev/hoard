<?php

namespace HoardPool\Test;

class MemcachedTest extends \Hoard\PoolTest
{

    protected function getPool()
    {
        try {
            $pool = \Hoard\CacheManager::getPool('test.memcached');
            $pool->clear();
            return $pool;
        }
        catch(\Exception $e) {
            if($e->getMessage() == 'Memcached extension is not installed.') {
                $this->markTestSkipped();
            }
            throw $e;
        }
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

