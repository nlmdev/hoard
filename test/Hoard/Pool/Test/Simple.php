<?php

namespace Hoard\Pool\Test;

class Simple extends \Hoard\AbstractPool
{

    public static function getAdapterName()
    {
        return 'transient';
    }

}

