<?php

namespace Hoard;

/**
 * Thrown when a pool is requested that does not exist. This will usually mean
 * that there is no class to represent the pool, not nessesarily that there is
 * no data available for that pool.
 */
class NoSuchPoolException extends Exception
{

    /**
     * Construct a NoSuchPoolException.
     * @param string $poolName The name of the pool that did not exist.
     * @param int $code Passed directly through to parent constructor.
     * @param \Exception $previous Passed directly through to parent constructor.
     */
    public function __construct($poolName, $code = 0, \Exception $previous = null)
    {
        parent::__construct("No such pool '{$poolName}'", $code, $previous);
    }

}

