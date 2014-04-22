<?php

namespace Hoard;

/**
 * Abstract storage adapter.
 */
abstract class AbstractAdapter implements AdapterInterface
{

    /**
     * @var array
     */
    protected $adapterOptions = array();

    /**
     * @var \Hoard\PoolInterface
     */
    protected $pool = null;

    /**
     * Set the pool instance for the circular DI dependency.
     */
    public function setPool($pool)
    {
        $this->pool = $pool;
    }

    public function __construct(array $adapterOptions = array())
    {
        $this->adapterOptions = $adapterOptions;
    }

}

