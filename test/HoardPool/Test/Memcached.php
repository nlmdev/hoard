<?php

namespace HoardPool\Test;

class Memcached extends \Hoard\AbstractPool
{

    public function getAdapterClass()
    {
        return '\Hoard\Adapter\Memcached';
    }

}

