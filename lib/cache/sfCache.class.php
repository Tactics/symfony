<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCache is an abstract class for all cache classes in symfony.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Fabien Marty <fab@php.net>
 *
 * @version    SVN: $Id: sfCache.class.php 3198 2007-01-08 20:36:20Z fabien $
 */
abstract class sfCache
{
    public const DEFAULT_NAMESPACE = '';
    /**
     * Cache lifetime (in seconds).
     *
     * @var int
     */
    protected $lifeTime = 86400;

    /**
     * Timestamp of the last valid cache.
     *
     * @var int
     */
    protected $refreshTime;

    /**
     * Gets the cache content for a given id and namespace.
     *
     * @param  string  The cache id
     * @param  string  The name of the cache namespace
     * @param  bool If set to true, the cache validity won't be tested
     *
     * @return string The data of the cache (or null if no cache available)
     */
    abstract public function get($id, $namespace = '', $doNotTestCacheValidity = false);

    /**
     * Returns true if there is a cache for the given id and namespace.
     *
     * @param  string  The cache id
     * @param  string  The name of the cache namespace
     * @param  bool If set to true, the cache validity won't be tested
     *
     * @return bool true if the cache exists, false otherwise
     */
    abstract public function has($id, $namespace = '', $doNotTestCacheValidity = false);

    /**
     * Saves some data in the cache.
     *
     * @param string The cache id
     * @param string The name of the cache namespace
     * @param string The data to put in cache
     *
     * @return bool true if no problem
     */
    abstract public function set($id, $namespace = '', $data = '');

    /**
     * Removes a content from the cache.
     *
     * @param string The cache id
     * @param string The name of the cache namespace
     *
     * @return bool true if no problem
     */
    abstract public function remove($id, $namespace = '');

    /**
     * Cleans the cache.
     *
     * If no namespace is specified all cache content will be destroyed
     * else only cache contents of the specified namespace will be destroyed.
     *
     * @param string The name of the cache namespace
     *
     * @return bool true if no problem
     */
    abstract public function clean($namespace = null, $mode = 'all');

    /**
     * Sets a new life time.
     *
     * @param int The new life time (in seconds)
     */
    public function setLifeTime($newLifeTime)
    {
        $this->lifeTime = $newLifeTime;
        $this->refreshTime = time() - $newLifeTime;
    }

    /**
     * Returns the current life time.
     *
     * @return int The current life time (in seconds)
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * Returns the cache last modification time.
     *
     * @return int The last modification time
     */
    abstract public function lastModified($id, $namespace = '');
}
