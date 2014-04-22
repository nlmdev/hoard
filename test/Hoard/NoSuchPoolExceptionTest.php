<?php

namespace Hoard;

class NoSuchPoolExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testNoSuchPoolExceptionExtendsException()
    {
        $this->assertInstanceOf('\Hoard\Exception', new NoSuchPoolException('pool.name'));
    }

    /**
     * @expectedException \Hoard\NoSuchPoolException
     * @expectedExceptionMessage No such pool 'pool.name'
     */
    public function testPoolNameReturnedInExceptionMessage()
    {
        throw new NoSuchPoolException('pool.name');
    }

}

