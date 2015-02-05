<?php

namespace Hoard\Adapter;

use Predis\Client as PredisClient;

/**
 * @brief Redis adapter.
 */
class Redis extends \Hoard\AbstractAdapter
{

    /**
     * @var PredisClient
     */
    protected $connection = null;

    /**
     * @brief This will be initialised automatically and is prepended to all keys.
     * @var string
     */
    protected static $prefixes = array();

    /**
     * This method cannot be overridden because you need to override \Hoard\AbstractPool::getAdapterOptions().
     * @return \Memcached
     */
    protected final function getConnection()
    {
        // set the servers to use with this connection
        $servers = array();
        if (array_key_exists('servers', $this->adapterOptions)) {
            $servers = $this->adapterOptions['servers'];
        }

        // first time connect
        if(null == $this->connection) {
            $this->connection = new PredisClient($servers);
        }
        return $this->connection;
    }

    /**
     * Get an item from the cache.
     * @param string $key The key.
     * @return \Hoard\Item
     */
    public function get($key)
    {
        $realKey = $this->getPrefix() . $key;
        if($this->getConnection()->type($realKey)->getPayload() === 'none') {
            return new \Hoard\Item($this->pool, $key, null, false);
        }
        $value = $this->getConnection()->get($realKey);
        return new \Hoard\Item($this->pool, $key, unserialize($value), true);
    }

    /**
     * Save item.
     * @param string $key The key.
     * @param mixed $value The unserialized value.
     * @param \DateTime $expireTime The absolute time this item must expire.
     * @return bool
     */
    public function set($key, $value, \DateTime $expireTime = null)
    {
        $realKey = $this->getPrefix() . $key;

        // check if key is expired already and delete just in case
        $now = new \DateTime();
        if ($now > $expireTime) {
            $this->getConnection()->del($realKey);

            return false;
        }

        // key hasn't expired so move on
        $r = $this->getConnection()->set($realKey, serialize($value));

        // expire the key if it has an expiry set
        if (null !== $expireTime) {
            $this->getConnection()->expireat($realKey, $expireTime->getTimestamp());
        }

        return $r;    }

    /**
     * Delete an item from the cache.
     * @param string $key The key.
     * @return bool
     */
    public function delete($key)
    {
        return $this->getConnection()->del($this->getPrefix() . $key);
    }

    /**
     * Get all the keys from the cache pool.
     * @return array
     */
    public function getKeys()
    {
        return $this->getConnection()->getAllKeys();
    }

    /**
     * This function is very important to how the memcache pools are able to
     * drop their contents. Every item stored in the cache has a prefix, this
     * prefix is based on a combination of the pool name and version. When
     * the version is incremented (or changed in any way) all the keys that
     * will be still be stored in the cache will be not in accessable and so
     * the pool is effectivly empty.
     *
     * To make this possible we have to keep a persisten version ID, which is
     * stored in the memcache server as well.
     *
     * @return string A string to be prepended to all keys.
     */
    public function getPrefix()
    {
        $poolName = $this->pool->getName();
        if(!array_key_exists($poolName, self::$prefixes)) {
            // we need a list of of the full class hierarchy, in order of
            // longest string last
            $classes = class_parents($this->pool);
            array_pop($classes);
            array_unshift($classes, get_class($this->pool));
            sort($classes);

            $prefix = "";
            foreach($classes as $class) {
                // try and get the version from the memcached server
                $self = new $class($this);
                $poolName = $self->getName();
                $versionKey = "{$poolName}::VERSION";
                $version = $this->getConnection()->get($versionKey);

                // if there was no version returned, we save it back now
                if(!ctype_digit($version)) {
                    $version = 1;
                    $this->getConnection()->set($versionKey, $version);
                }
                
                // set the prefix
                $prefix .= "{$poolName}::{$version}::";
            }

            self::$prefixes[$poolName] = $prefix;
        }
        return self::$prefixes[$poolName];
    }

    /**
     * Drop all of the items in the cache.
     * @return Always TRUE.
     */
    public function clear()
    {
        // clearing the cache means we increment the version, making all the
        // previous keys unreachable.
        $poolName = $this->pool->getName();
        $key = "{$poolName}::VERSION";
        $item = $this->get($key);
        if(!ctype_digit($item->get())) {
            $item->set('0');
        }
        $this->getConnection()->incr($key);

        // invalidate all prefixes for all other pool, even though this will
        // cause pool that are unrelated to be refreshed
        self::$prefixes = array();

        return true;
    }

}
