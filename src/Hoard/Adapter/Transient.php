<?php

namespace Hoard\Adapter;

/**
 * @brief This is a simple adapter that stores its cache in an array.
 * This can be used to store items very quickly that only need to exist as long
 * as the current PHP process. It is also used to define all the expected
 * behaviour for unit tests - those tests are applied to all the other drivers.
 */
class Transient extends \Hoard\AbstractAdapter
{

    /**
     * The actual cache data.
     * @var array
     */
    protected $data = array();

    /**
     * Test if an item exists in the cache.
     * @param string $key The key.
     * @return TRUE if the item exists and has not expired yet, FALSE otherwise.
     */
    public function exists($key)
    {
        // first check if the key exists at all
        if(!array_key_exists($key, $this->data)) {
            return false;
        }

        // if the key does exist, we still need to verify the expire time
        $obj = $this->data[$key];
        return (new \DateTime() < $obj['expire']);
    }

    /**
     * Get an item from the cache.
     * @param string $key The key.
     * @return \Hoard\Item
     */
    public function get($key)
    {
        if($this->exists($key)) {
            $obj = $this->data[$key];
            return new \Hoard\Item($this->pool, $key, $obj['data'], true);
        }
        return new \Hoard\Item($this->pool, $key, null, false);
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
        $exists = $this->exists($key);
        $this->data[$key] = array(
            'expire' => $expireTime,
            'data' => $value
        );
        return $exists;
    }

    /**
     * Delete an item from the cache.
     * @param string $key The key.
     * @return bool
     */
    public function delete($key)
    {
        $exists = $this->exists($key);
        unset($this->data[$key]);
        return $exists;
    }

    /**
     * Get all the keys from the cache pool.
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->data);
    }

    /**
     * Drop all of the items in the cache.
     * @return Always TRUE.
     */
    public function clear()
    {
        $this->data = array();
        return true;
    }

}

