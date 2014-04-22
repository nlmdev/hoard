<?php

namespace HoardPool\Test;

class Memcached extends \Hoard\AbstractPool
{

    public function getAdapterClass()
    {
        return '\Hoard\Adapter\Memcached';
    }

    public function getAdapterOptions()
    {
        return array(
            'servers' => array(
                array(
                    'host' => 'nlm-taste-ci.elnm2a.0001.apse2.cache.amazonaws.com',
                    'port' => 11211
                )
            )
        );
    }

}

