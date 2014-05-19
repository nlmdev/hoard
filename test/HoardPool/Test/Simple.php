<?php

namespace HoardPool\Test;

class Simple extends \Hoard\AbstractPool
{

    public function getAdapterClass()
    {
        return '\Hoard\Adapter\Transient';
    }

    public function getAdapterOptions()
    {
        return array(
            'foo' => 'bar'
        );
    }

}
