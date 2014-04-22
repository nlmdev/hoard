<?php

namespace Hoard;

class ScriptRunnerTest extends \PHPUnit_Framework_TestCase
{

    protected $pool;

    protected $runner;

    public function setUp()
    {
        $this->runner = new ScriptRunner('');

        try {
            // create our running application cache
            $this->pool = CacheManager::getPool('test.memcached');
            $this->pool->clear();
        }
        catch(\Exception $e) {
            if($e->getMessage() == 'Memcached extension is not installed.') {
                $this->markTestSkipped();
            }
            throw $e;
        }

        $this->add('mykey1', 'some data');
        $this->add('mykey2', 'some other data');
        $this->add('mykey3', 123);
    }

    protected function add($key, $data)
    {
        $item = $this->pool->getItem($key);
        $item->set($data);
    }

    public function testDropEntirePool()
    {
        // drop test.memcached
        $this->runner->drop(array('test.memcached'));

        $this->assertFalse($this->pool->getItem('mykey1')->isHit());
        $this->assertFalse($this->pool->getItem('mykey2')->isHit());
        $this->assertFalse($this->pool->getItem('mykey3')->isHit());
    }

    public function testDropSingleKey()
    {
        // drop test.memcached:mykey2
        $this->runner->drop(array('test.memcached:mykey2'));

        $this->assertTrue($this->pool->getItem('mykey1')->isHit());
        $this->assertFalse($this->pool->getItem('mykey2')->isHit());
        $this->assertTrue($this->pool->getItem('mykey3')->isHit());
    }

    public function testDropMultipleKeys()
    {
        // drop test.memcached:mykey1,mykey3
        $this->runner->drop(array('test.memcached:mykey1,mykey3'));

        $this->assertFalse($this->pool->getItem('mykey1')->isHit());
        $this->assertTrue($this->pool->getItem('mykey2')->isHit());
        $this->assertFalse($this->pool->getItem('mykey3')->isHit());
    }

}

