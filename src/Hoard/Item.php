<?php

namespace Hoard;

/**
 * @brief Represents an item that may or may not exist in the cache.
 */
class Item implements ItemInterface
{

    /**
     * This is the original pool that the item belongs to.
     * @var \Hoard\PoolInterface
     */
    protected $pool;

    /**
     * The key.
     * @var string
     */
    protected $key;

    /**
     * The unserialized value.
     * @var mixed
     */
    protected $value;

    /**
     * If this is true then the item cacme from the cache, otherwise this item
     * represents something that has not yet been saved to cache.
     * @var bool
     */
    protected $isHit;

    /**
     * @brief Construct a new Item.
     * You should never use this directly. It is used internally to create items
     * from the pool.
     * @param \Hoard\PoolInterface $pool The pool that created this item.
     * @param string $key The key.
     * @param mixed $value The unserialized value, retaining the original type.
     * @param bool $isHit Was this item retrived from cache?
     */
    public function __construct(\Hoard\PoolInterface $pool, $key, $value, $isHit)
    {
        $this->pool = $pool;
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
    }

    /**
     * Get the cache key.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the unserialized value from cache.
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Set (save) the item back to the cache pool.
     * @param mixed $value The unserialized value to store.
     * @param mixed $ttl This may be NULL to mean that it will fall back to
     * getDefaultExpireTime(), if that is not overriden then the item will
     * never expire. If it's and int it will be used as the number of seconds
     * from now until the item expire. Finally you can specify a \DateTime for
     * an absolute expire time.
     * @param bool $shouldNotBeEmpty (true) If set to false, then empty value will be treated as error
     * @throws InvalidArgumentException when TTL isn't a valid value
     * @return TRUE on success, FALSE otherwise.
     */
    public function set($value = null, $ttl = null, $shouldNotBeEmpty = false)
    {
        if ($shouldNotBeEmpty && empty($value)) {
            // log error
            $logger = $this->pool->getLogger();
            if (isset($logger)) {
                $logger->alert(
                    'Caching empty value',
                    array(
                        'key' => $this->key,
                        'value' => $value,
                        'request_uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null
                    )
                );
            }
        }

        $this->value = $value;
        if(null === $ttl) {
            $expireTime = $this->pool->getDefaultExpireTime();
        } elseif($ttl instanceof \DateTime) {
            $expireTime = $ttl;
        } elseif(is_int($ttl)) {
            $expireTime = new \DateTime("+{$ttl} second");
        } else {
            throw new InvalidArgumentException("Invalid argument for TTL.");
        }

        // update the isHit cache
        $this->isHit = true;

        // handle through the pool implementation
        return $this->pool->getAdapter()->set($this->key, $this->value, $expireTime);
    }

    /**
     * @brief Check if this item was pulled out of cache (cache hit).
     * @note The PSR-6 standard allows us to not send another request through
     * the adapter, we can use the information we already have about the item
     * @return bool
     */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * @brief Delete the item from cache.
     * For all intents and purposes this is the same as setting the expiry date
     * to before now.
     * @return TRUE on success, FALSE if the item never existed in the cache.
     */
    public function delete()
    {
        // update the isHit cache
        $this->isHit = false;

        // handle through the pool implementation
        return $this->pool->getAdapter()->delete($this->getKey());
    }

    /**
     * @brief Alias for isHit().
     * @return bool
     */
    public function exists()
    {
        return $this->isHit();
    }

}
