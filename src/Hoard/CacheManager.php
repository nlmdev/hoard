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
     * @var \Psr\Logger\LoggerInterface
     */
    protected static $defaultLogger = null;

    /**
     * Get a pool by name.
     * @param string $poolName The name of the pool.
     * @param array $config Optional configuration for adapter.
     * @return \Hoard\AbstractPool
     */
    public static function getPool($poolName, array $config = array())
    {
        $className = self::getClassFromPoolName($poolName);
        if(!class_exists($className)) {
            throw new NoSuchPoolException($poolName);
        }

        // create pool
        $pool = new $className();
        $adapterOptions = $pool->getAdapterOptions();

        // create adapter
        if(array_key_exists('adapter', $adapterOptions)) {
            $adapterClass = $adapterOptions['adapter'];
        } else {
            $adapterClass = $pool->getAdapterClass();
        }

        $adapter = new $adapterClass($adapterOptions);
        $adapter->setPool($pool);
        $pool->setAdapter($adapter);

        // attach the default logger
        $pool->setLogger(self::getDefaultLogger());

        return $pool;
    }

    /**
     * @return \Psr\Logger\LoggerInterface
     */
    public static function getDefaultLogger()
    {
        if(null === self::$defaultLogger) {
            self::$defaultLogger = new \Monolog\Logger('DefaultHoardLogger');
        }
        return self::$defaultLogger;
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

    /**
     * Set the default logging instance.
     * @param \Psr\Log\LoggerInterface $logger the logger to send all messages
     * to.
     * @return true
     */
    public static function setDefaultLogger(\Psr\Log\LoggerInterface $logger)
    {
        self::$defaultLogger = $logger;
        return true;
    }

}
