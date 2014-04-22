<?php

namespace Hoard;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{

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

