<?php

namespace Hoard\Adapter;

/**
 * @brief Memcached adapter.
 */
class Memcached extends \Hoard\AbstractAdapter
{

    /**
     * @var \Memcached
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
        // first time connect
        if(null == $this->connection) {
            $this->connection = new \Memcached();

            // add servers
            foreach($this->adapterOptions['servers'] as $server) {
                list($host, $port) = explode(':', $server, 2);
                $this->connection->addServer($host, $port);
            }
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
        $value = $this->getConnection()->get($this->getPrefix() . $key);
        if($this->getConnection()->getResultCode() == \Memcached::RES_NOTFOUND) {
            return new \Hoard\Item($this->pool, $key, null, false);
        }
        return new \Hoard\Item($this->pool, $key, $value, true);
    }

    /**
     * Save item.
     * @param string $key The key.
     * @param mixed $value The unserialized value.
     * @param \DateTime $expireTime The absolute time this item must expire.
     * @return bool
     */
    public function set($key, $value, \DateTime $expireTime)
    {
        // To prevent the clock in the memcache server becoming out of sync
        // with that of the applicaton server we are allowed to specify the
        // seconds upto 1 month. Recognise this and handle appropriately.
        $month = 30 * 24 * 3600;
        $now = new \DateTime();
        $diff = $expireTime->getTimestamp() - $now->getTimestamp();
        if($diff < $month) {
            $expire = $diff;
        }
        else {
            $expire = $expireTime->getTimestamp();
        }

        $this->getConnection()->set($this->getPrefix() . $key, $value, $expire);
        return $this->getConnection()->getResultCode() == \Memcached::RES_SUCCESS;
    }

    /**
     * Delete an item from the cache.
     * @param string $key The key.
     * @return bool
     */
    public function delete($key)
    {
        return $this->getConnection()->delete($this->getPrefix() . $key);
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
                if(!is_int($version)) {
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
        $this->getConnection()->increment("{$poolName}::VERSION");

        // invalidate all prefixes for all other pool, even though this will
        // cause pool that are unrelated to be refreshed
        self::$prefixes = array();

        return true;
    }

}

