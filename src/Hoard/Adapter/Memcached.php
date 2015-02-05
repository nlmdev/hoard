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
     *
     * @return \Memcached
     * @throws \Exception
     */
    protected final function getConnection()
    {
        $defaultPersistentId = 'hoard-persist';

        // first time connect
        if(null == $this->connection) {
            if(!class_exists('\Memcached')) {
                throw new \Exception('Memcached extension is not installed.');
            }

            $this->connection = new \Memcached($defaultPersistentId);

            if (!count($this->connection->getServerList())) {
                // setup memcached options
                if (isset($this->adapterOptions['client']) && is_array($this->adapterOptions['client'])) {
                    foreach ($this->adapterOptions['client'] as $name => $value) {
                        $optId = null;

                        if (is_int($name)) {
                            $optId = $name;
                        } else {
                            $optConst = 'Memcached::OPT_' . strtoupper($name);

                            if (defined($optConst)) {
                                $optId = constant($optConst);
                            } else {
                                throw new \Exception("Unknown memcached client option '{$name}' ({$optConst})");
                            }
                        }

                        if (null !== $optId) {
                            if (!$this->connection->setOption($optId, $value)) {
                                throw new \Exception("Setting memcached client option '{$optId}' failed");
                            }
                        }
                    }
                }

                // if no servers are provided try to bind to default configuration
                if(!array_key_exists('servers', $this->adapterOptions)) {
                    $this->adapterOptions['servers'] = array(
                        'localhost:11211'
                    );
                }

                // add servers
                foreach($this->adapterOptions['servers'] as $server) {
                    list($host, $port) = explode(':', $server, 2);
                    $this->connection->addServer($host, $port);
                }
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

    public function validateKey($key)
    {
        for($i = 0; $i < strlen($key); ++$i) {
            if(ctype_cntrl($key[$i])) {
                throw new \Hoard\InvalidArgumentException("Keys cannot contain control characters.");
            }
            if(ctype_space($key[$i])) {
                throw new \Hoard\InvalidArgumentException("Keys cannot contain whitespace characters.");
            }
        }
    }

    /**
     * Save item.
     * @param string $key The key.
     * @param mixed $value The unserialized value.
     * @param \DateTime $expireTime The absolute time this item must expire.
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($key, $value, \DateTime $expireTime)
    {
        $this->validateKey($key);

        // saving keys list
        $keysList = array();
        $keyFoundinList = false;
        $keyStoreId = $this->pool->getName().'::KEYLIST';
        if ($encodedKeysList = $this->getConnection()->get($keyStoreId)) {
            $keysList = json_decode($encodedKeysList);
            foreach ($keysList as $tempKey) {
                if ($tempKey == $key) {
                    $keyFoundinList = true;
                    break;
                }
            }
        }

        if (false == $keyFoundinList) {
            $keysList[] = $key;
        }

        $encodedKeysList = json_encode($keysList);
        $this->getConnection()->set($keyStoreId, $encodedKeysList, 86400 * 30);

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
