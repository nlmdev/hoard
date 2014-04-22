<?php

namespace Hoard;

abstract class AdapterTest extends \PHPUnit_Framework_TestCase
{

    protected abstract function getAdapter();

    public function setUp()
    {
        $this->adapter = $this->getAdapter();
    }

    protected function assertNoSuchItem(\Hoard\ItemInterface $item, $expectedKey)
    {
        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());
        $this->assertEquals($expectedKey, $item->getKey());
    }

    public function testNoSuchKey()
    {
        $item = $this->adapter->get('nosuchkey');
        $this->assertNoSuchItem($item, 'nosuchkey');
    }

    public function testSetNewKey()
    {
        $this->assertFalse($this->adapter->set('mykey', 'mydata', new \DateTime('+1 day'), false));
        return $this->adapter;
    }

    public function testGetKey()
    {
        $this->assertFalse($this->adapter->set('mykey', 'mydata', new \DateTime('1st January 2020'), false));
        $item = $this->adapter->get('mykey');
        $this->assertInstanceOf('\Hoard\ItemInterface', $item);
        $this->assertEquals('mydata', $item->get());
    }

    /**
     * @depends testSetNewKey
     */
    public function testReplaceExistingKey($adapter)
    {
        $this->assertTrue($adapter->set('mykey', 'mydata2', new \DateTime('+1 day')));
        $this->assertEquals('mydata2', $adapter->get('mykey')->get());
    }

    /**
     * @depends testSetNewKey
     */
    public function testDeleteExistingKey($adapter)
    {
        $this->assertTrue($adapter->delete('mykey'));
    }

    public function testDeleteNonExistingKey()
    {
        $this->assertFalse($this->adapter->delete('mykey')); 
    }

    public function getKeysFromEmptyPool()
    {
        $this->assertTrue(array() == $this->adapter->getKeys());
    }

    public function getKeysFromPopulatedPool()
    {
        $this->adapter->set('mykey1', 'mydata');
        $this->adapter->set('mykey2', 'mydata');
        $this->assertTrue(array('mykey1', 'mykey2') == $this->adapter->getKeys());
    }

}

