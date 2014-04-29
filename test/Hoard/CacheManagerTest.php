<?php

namespace Hoard;

class DummyLogger extends \Psr\Log\AbstractLogger
{

    public function log($level, $message, array $context = array())
    {
        // do nothing
    }

}

class DummyAdapter extends \Hoard\Adapter\Memcached
{
}

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testWillUseMemcacheAdapterIfNotProvided()
    {
        $pool = CacheManager::getPool('test.simple');
        $this->assertInstanceOf('\Hoard\Adapter\Memcached', $pool->getAdapter());
    }

    public function testWillUseAdapterIfProvided()
    {
        $pool = CacheManager::getPool('test.custom_adapter');
        $this->assertInstanceOf('\Hoard\DummyAdapter', $pool->getAdapter());
    }

    public function testWillSetAdapterOptionsWhenCreatingThePool()
    {
        $pool = CacheManager::getPool('test.simple');
        $this->assertEquals($pool->getAdapter()->getAdapterOptions(), array('foo' => 'bar'));
    }

    public function testDefaultLoggerIsASingleton()
    {
        $a = CacheManager::getDefaultLogger();
        $b = CacheManager::getDefaultLogger();
        $this->assertSame($a, $b);
    }

    public function testDefaultLoggerWillBeSetOnInitialisation()
    {
        $pool = CacheManager::getPool('test.simple');
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $pool->getLogger());
    }

    public function testSetDefaultLoggerWillAlwaysReturnTrue()
    {
        $r = CacheManager::setDefaultLogger(new DummyLogger());
        $this->assertTrue($r);
    }

    public function testCanSetADefaultLogger()
    {
        CacheManager::setDefaultLogger(new DummyLogger());
    }

    /**
     * @expectedException \Hoard\NoSuchPoolException
     * @expectedExceptionMessage No such pool 'does.not.exist'
     */
    public function testNoSuchPoolThrowsException()
    {
        CacheManager::getPool('does.not.exist');
    }

    public function testGetPool()
    {
        $pool = CacheManager::getPool('test.simple');
        $this->assertInstanceOf('\Hoard\PoolInterface', $pool);
    }

    public function testCacheManagerUsesCorrectInterface()
    {
        $this->assertTrue(is_subclass_of('\Hoard\CacheManager', '\Hoard\CacheManagerInterface'));
    }

    public function testTheNamespaceForPoolsIsCorrect()
    {
        $this->assertSame('\HoardPool', CacheManager::getPoolNamespace());
    }

    /**
     * @dataProvider translationOfPoolNamesToClassNamesData
     */
    public function testTranslationOfPoolNamesToClassNames($poolName, $className)
    {
        $this->assertSame(CacheManager::getClassFromPoolName($poolName), $className);
    }

    public function translationOfPoolNamesToClassNamesData()
    {
        return array(
            'simple' => array('basic', CacheManager::getPoolNamespace() . '\Basic'),
            'nested' => array('nested.pools', CacheManager::getPoolNamespace() . '\Nested\Pools'),
            'sentence' => array('nested_pool.with_sentencecase.name', CacheManager::getPoolNamespace() . '\NestedPool\WithSentencecase\Name')
        );
    }

}

