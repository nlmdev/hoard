<?php

namespace Hoard;

class GenericPool extends AbstractPool
{

    /**
     * I'm not sure if this is actually important, basically 'transient' is the
     * name of the adapter in the application ini, but we have already provided
     * the concrete adapter so its unlikely this value will be used for its
     * original purpose
     */
    public static function getAdapterName()
    {
        return 'transient';
    }

}

class PoolTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * This is quite simple, we swap out whatever adapter is normally used by
     * the pool with the transient adapter and prepopulate the items.
     * @param array $items Items to be prepopulated in the pool.
     * @param string $poolName If you provide a pool name then the mocked pool
     * returned will contain all the same business logic.
     */
    public function getMockedPool(array $items = array(), $poolName = '')
    {
        // this is quite simple, we swap out whatever adapter is normally used
        // by the pool with the transient adapter
        $adapter = new \Hoard\Adapter\Transient();

        // get the class name for the pool
        if(empty($poolName)) {
            $className = 'Hoard\GenericPool';
        }
        else {
            $className = \Hoard\CacheManager::getClassFromPoolName($poolName);
        }

        // mock the original business logic
        $mock = $this->getMock($className, null, array($adapter));
        $adapter->setPool($mock);

        // load in the items
        foreach($items as $key => $value) {
            $item = $mock->getItem($key);
            $item->set($value);
        }

        return $mock;
    }

    public static function provideMockedPool(array $items = array(), $poolName = '')
    {
        $self = new self();
        return $self->getMockedPool($items, $poolName);
    }

}
