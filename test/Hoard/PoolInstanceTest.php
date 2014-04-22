<?php

namespace Hoard;

class PoolInstanceTest extends \Hoard\PoolTestCase
{

    public function testMockingPool()
    {
        // create mock
        $pool = $this->getMockedPool(array(
            'mykey' => 'myvalue'
        ));

        // found item
        $item = $pool->getItem('mykey');
        $this->assertEquals($item->get(), 'myvalue');
        $this->assertTrue($item->isHit());

        // not found item
        $item = $pool->getItem('mykey2');
        $this->assertFalse($item->isHit());
    }

}

