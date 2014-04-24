<?php

namespace Hoard;

/**
 * Any adapter (also called a driver) that implements the lowest level of
 * abstract storage will implement this.
 */
interface AdapterInterface
{

    /**
     * @brief The item key.
     * @param string $key The key.
     * @return \Hoard\Item
     * @throws \Hoard\NoSuchKeyException if the \p $key does not exist.
     */
    public function get($key);

    /**
     * @brief Set an item for the given key.
     * @param string $key The key.
     * @param mixed $data The raw data to be stored.
     * @param \DateTime $expire The absolute time when this object is to be
     * considered expired.
     * @return TRUE is they key already existed, FALSE is the data was set onto
     * a previously unpopulated key.
     * @throws \Hoard\KeyAlreadyExistsException if \p $allowOverride is
     * FALSE and the key already exists in the cache.
     */
    public function set($key, $value, \DateTime $expire);

    /**
     * @brief Delete (immediately expire) an item by key.
     * @note The adapter will not guarentee that the memory (or even the raw
     * data) is indeed release. It only makes the promoise that the data
     * attached to this key can no longer be retrieved and for all intents
     * and purposes will work as if the key does not exist.
     * @param string $key The key.
     * @return TRUE if the \p $key exists and it has been removed, FALSE on
     * is no action occured.
     */
    public function delete($key);

    /**
     * @brief Get a list of all keys available.
     * @note This provides no paging or restriction on how many keys will be
     * returned, but does guarentee that all the keys returned will be for the
     * entire pool. This make this function possibly very memory hungry and
     * could cause PHP to run out of memory on a large pool.
     * @return array Of unique keys. There is no promise that the keys will be
     * sorted.
     */
    public function getKeys();

    /**
     * Drop all items in the cache.
     * @return Always TRUE.
     */
    public function clear();

    public function setAdapterOptions(array $options);

    public function getAdapterOptions();

}

