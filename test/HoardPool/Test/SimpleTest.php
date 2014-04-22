<?php

namespace HoardPool\Test;

class SimpleTest extends \Hoard\PoolTest
{

    protected function getPool()
    {
        try {
            $pool = \Hoard\CacheManager::getPool('test.simple');
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

    public function testPoolName()
    {
        $this->assertEquals($this->getPool()->getName(), 'test.simple');
    }

}
