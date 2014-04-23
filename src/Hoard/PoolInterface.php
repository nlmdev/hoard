<?php

namespace Hoard;

/**
 * All cache pools will implement this interface.
 */
interface PoolInterface extends \Psr\Cache\PoolInterface
{

    public function getAdapterOptions();

}

