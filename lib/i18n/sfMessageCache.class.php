<?php
/**
 * Translation table cache.
 *
 * @author     Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version    $Id: sfMessageCache.class.php 6806 2007-12-29 07:53:10Z fabien $
 */

/**
 * Cache the translation table into the file system.
 * It can cache each cataloug+variant or just the whole section.
 *
 * @author $Author: weizhuo $
 *
 * @version $Id: sfMessageCache.class.php 6806 2007-12-29 07:53:10Z fabien $
 */
class sfMessageCache
{
    /**
     * Cache Lite instance.
     */
    protected $cache;

    /**
     * Cache life time, default is 1 year.
     */
    protected $lifetime = 3153600;

    /**
     * Creates a new Translation cache.
     */
    public function initialize($options = [])
    {
        $this->cache = new sfFileCache();
        $this->cache->initialize($options);
    }

    /**
     * Gets the cache life time.
     *
     * @return int cache life time
     */
    public function getLifeTime()
    {
        return $this->lifetime;
    }

    /**
     * Sets the cache life time.
     *
     * @param int $time cache life time
     */
    public function setLifeTime($time)
    {
        $this->lifetime = intval($time);
    }

    /**
     * Gets the cache file ID based section and locale.
     *
     * @param string $catalogue the translation section
     * @param string $culture   The translation locale, e.g. "en_AU".
     *
     * @return string
     */
    protected function getID($catalogue, $culture)
    {
        return $culture;
    }

    /**
     * Gets the cache file GROUP based section and locale.
     *
     * @param string $catalogue the translation section
     * @param string $culture   The translation locale, e.g. "en_AU".
     *
     * @return string
     */
    protected function getGroup($catalogue, $culture)
    {
        return $catalogue;
    }

    /**
     * Gets the data from the cache.
     *
     * @param string $catalogue the translation section
     * @param string $culture   The translation locale, e.g. "en_AU".
     *
     * @return mixed Boolean FALSE if no cache hit. Otherwise, translation
     *               table data for the specified section and locale.
     */
    public function get($catalogue, $culture, $lastmodified = 0)
    {
        $ID = $this->getID($catalogue, $culture);
        $group = $this->getGroup($catalogue, $culture);

        if ($lastmodified <= 0 || $lastmodified > $this->cache->lastModified($ID, $group)) {
            return false;
        }

        return unserialize($this->cache->get($ID, $group));
    }

    /**
     * Saves the data to cache for the specified section and locale.
     *
     * @param array  $data      the data to save
     * @param string $catalogue the translation section
     * @param string $culture   The translation locale, e.g. "en_AU".
     */
    public function save($data, $catalogue, $culture)
    {
        $ID = $this->getID($catalogue, $culture);
        $group = $this->getGroup($catalogue, $culture);

        return $this->cache->set($ID, $group, serialize($data));
    }

    /**
     * Cleans up the cache for the specified section and locale.
     *
     * @param string $catalogue the translation section
     * @param string $culture   The translation locale, e.g. "en_AU".
     */
    public function clean($catalogue, $culture)
    {
        $group = $this->getGroup($catalogue, $culture);
        $this->cache->clean($group);
    }

    /**
     * Flushes the cache. Deletes all the cache files.
     */
    public function clear()
    {
        $this->cache->clean();
    }
}
