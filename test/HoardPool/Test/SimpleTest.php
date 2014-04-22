<?php

namespace HoardPool\Test;

class SimpleTest extends \Hoard\PoolTest
{

    protected function getPool()
    {
        $pool = \Hoard\CacheManager::getPool('test.simple');
        $pool->clear();
        return $pool;
    }

    public function testPoolName()
    {
        $this->assertEquals($this->getPool()->getName(), 'test.simple');
    }

}
