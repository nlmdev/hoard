<?php

namespace HoardPool\Test;

class Redis extends \Hoard\AbstractPool
{

    public function getAdapterClass()
    {
        return '\Hoard\Adapter\Redis';
    }

}
