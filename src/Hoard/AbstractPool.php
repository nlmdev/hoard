<?php

namespace Hoard;

/**
 * An abstract pool.
 */
abstract class AbstractPool implements PoolInterface
{

    /**
     * @var \Hoard\AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var \Psr\Log\AbstractLogger
     */
    protected $logger = null;

    /**
     * Create a pool with an adapter.
     * @param \Hoard\AdapterInterface $adapter The adapter.
     */
    public function __construct(\Hoard\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Set the logger.
     * @var \Psr\Log\AbstractLogger $logger The logger instance.
     */
    public function setLogger(\Psr\Log\AbstractLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the adapter for this pool.
     * @return \Hoard\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @brief Get the optional adapter name.
     * If you do not provide this then the 'default' adapter will be used.
     */
    public static function getAdapterName()
    {
        return 'default';
    }

    /**
     * @brief Get an item from cache.
     * This function will always return \Hoard\ItemInterface even if there
     * is no such key in the cache.
     * @return \Hoard\ItemInterface
     */
    public function getItem($key)
    {
        $item = $this->getAdapter()->get($key);
        if(null !== $this->logger) {
            $logger->info("{pool}.{key}", array(
                'category' => 'cache',
                'pool' => $this->getName(),
                'key' => $key,
                'isHit' => $item->isHit()
            ));
        }
        return $item;
    }

    /**
     * Get all the items i the cache.
     * @note This has no restriction and so a large array of keys could cause
     * PHP to run out of the memory.
     * @param array $keys An array of keys to fetch.
     */
    public function getItems(array $keys)
    {
        $items = array();
        foreach($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return $items;
    }

    /**
     * Drop all of the items in this pool.
     * @return \Hoard\AbstractPool This.
     */
    public function clear()
    {
        $this->getAdapter()->clear();
        return $this;
    }

    /**
     * Get the name of the pool.
     * @return string
     */
    public function getName()
    {
        $className = substr(get_class($this), strlen(CacheManager::getPoolNamespace()));
        $lower = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $className));
        $poolName = str_replace("\\", '.', $lower);
        return $poolName;
    }

    /**
     * You may optionally override this with custom logic to handle nested
     * pools. Ideally you want to override this returning a string that
     * contains the pool name and version combination. As each key (any level
     * depth) contains the combination of all the parent getPrefix() then you
     * can be sure that changing the prefix of parent pool will invalidate the
     * child pools (as expected).
     * @return string
     */
    public function getPrefix()
    {
        return "";
    }

    /**
     * When an item is saved to the cache it will use the expire time provided
     * with the save call. However, if there is no expiry given then the value
     * returned by this method is used. You may override this method in your
     * respective and nexted pools for custom default expiry times.
     * @note You must return a \DateTime that is the absolute time when the
     *       item will expire.
     * @return \DateTime
     */
    public function getDefaultExpireTime()
    {
        // if you wish to override this with a relative value you can use:
        //     return new \DateTime("+2 day");
        return self::neverExpire();
    }

    /**
     * Use when you want something to stay in the cache 'forever'.
     */
    public static function neverExpire()
    {
        return new \DateTime('1st January 2038');
    }

}

