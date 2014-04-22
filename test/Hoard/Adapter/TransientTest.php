<?php

namespace Hoard\Adapter;

class TransientTest extends \Hoard\AdapterTest
{

    public function getAdapter()
    {
        $pool = $this->getMock('\Hoard\PoolInterface');
        $adapter = new Transient();
        $adapter->setPool($pool);
        return $adapter;
    }

}
