<?php

namespace Hoard;

/**
 * The cache manager handles all of the available pools.
 */
class CacheManager implements CacheManagerInterface
{

    /**
     * Use this constant to expire in one day.
     */
    const DAY = 86400;

    /**
     * Use this constant to expire in an hour.
     */
    const HOUR = 3600;

    /**
     * Use this constant to expire in 30 minutes.
     */
    const HALF_HOUR = 1800;

    /**
     * Use this constant to expire in 10 minutes.
     */
    const TEN_MINUTES = 600;

    /**
     * Use this constant to expire in a minute.
     */
    const MINUTE = 60;

    /**
     * Get a pool by name.
     * @param string $poolName The name of the pool.
     */
    public static function getPool($poolName)
    {
        $className = self::getClassFromPoolName($poolName);
        if(!class_exists($className)) {
            throw new NoSuchPoolException($poolName);
        }

        // create adapter
        $adapterClass = '\Hoard\Adapter\Memcached';
        $adapter = new $adapterClass($config);

        // create pool
        $pool = new $className($adapter);
        $adapter->setPool($pool);
        return $pool;
    }

    /**
     * Translate a pool name into its fully qualified concrete class name.
     * @param string $poolName The name of the pool.
     */
    public static function getClassFromPoolName($poolName)
    {
        $parts = explode('.', $poolName);
        foreach($parts as $partId => $part) {
            $parts[$partId] = str_replace(' ', '', ucwords(str_replace('_', ' ', $part)));
        }
        return self::getPoolNamespace() . "\\" . implode("\\", $parts);
    }

    /**
     * The namespace that contains the pool classes.
     * @return string
     */
    public static function getPoolNamespace()
    {
        return '\HoardPool';
    }

}
