<?php

namespace Hoard;

/**
 * Defines functionality provided by a CacheManager.
 */
interface CacheManagerInterface
{

    /**
     * @brief Request a pool by name.
     * Pool names are mapped to the respective classes via dot notation. The
     * pool 'my.cool.pool' will instantiate \BaseNamespace\My\Cool\Pool where
     * BaseNamespace is defined by CacheManagerInterface::getPoolNamespace()
     * @throws \Hoard\NoSuchPoolException if the pool does not exist.
     */
    public static function getPool($name);

    /**
     * @brief Get the fully qualified namespace prefix for pools.
     * @return string The fully qualified namespace prefix for pool classes.
     */
    public static function getPoolNamespace();

    /**
     * Set the default logging instance.
     * @param \Psr\Log\LoggerInterface $logger the logger to send all messages
     * to.
     * @return true
     */
    public static function setDefaultLogger(\Psr\Log\LoggerInterface $logger);

}
