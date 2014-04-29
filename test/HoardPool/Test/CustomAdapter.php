<?php

namespace HoardPool\Test;

class CustomAdapter extends Simple
{

    public function getAdapterOptions()
    {
        return array(
            'adapter' => '\Hoard\DummyAdapter'
        );
    }

}

