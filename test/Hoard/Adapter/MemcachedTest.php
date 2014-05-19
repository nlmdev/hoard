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
        $adapter->validateKey("a\n");
    }

    /**
     * @expectedException \Hoard\InvalidArgumentException
     * @expectedExceptionMessage Keys cannot contain whitespace characters.
     */
    public function testKeyWillNotAcceptWhitespaceCharacters()
    {
        $adapter = new \Hoard\Adapter\Memcached();
        $adapter->validateKey(" a");
    }

    public function testKeyWillBeValidatedOnSet()
    {
        if(!class_exists('Memcached')) {
            $this->markTestSkipped();
        }

        $key = 'abc';
        $adapter = $this->getMock('\Hoard\Adapter\Memcached', array('validateKey', 'getPrefix'));
        $adapter->expects($this->once())
                ->method('validateKey')
                ->with($key)
                ->will($this->returnValue(null));
        $adapter->expects($this->any())
                ->method('getPrefix')
                ->will($this->returnValue(''));

        $adapter->set($key, '', new \DateTime());
    }

}
