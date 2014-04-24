<?php

namespace Hoard\Adapter;

class MemcachedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Hoard\InvalidArgumentException
     * @expectedExceptionMessage Keys cannot contain control characters.
     */
    public function testKeyWillNotAcceptControlCharacters()
    {
        $adapter = new \Hoard\Adapter\Memcached();
        $adapter->validateKey("\n");
    }

    /**
     * @expectedException \Hoard\InvalidArgumentException
     * @expectedExceptionMessage Keys cannot contain whitespace characters.
     */
    public function testKeyWillNotAcceptWhitespaceCharacters()
    {
        $adapter = new \Hoard\Adapter\Memcached();
        $adapter->validateKey(" ");
    }

}
