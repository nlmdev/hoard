<?php

namespace HoardPool\Test;

class Simple extends \Hoard\AbstractPool
{

    public static function getAdapterName()
    {
        return 'transient';
    }

    public function getAdapterOptions()
    {
        return array(
            'adapter' => 'options'
        );
    }

}

