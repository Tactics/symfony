<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfGeneratorManager.class.php 9260 2008-05-26 10:18:04Z fabien $
 */
class sfGeneratorManager
{
    protected $cache;

    /**
     * Initializes the sfGeneratorManager instance.
     */
    public function initialize()
    {
        // create cache instance
        $this->cache = new sfFileCache(sfConfig::get('sf_module_cache_dir'));
        $this->cache->initialize(['lifeTime' => 86400 * 365 * 10, 'automaticCleaningFactor' => 0]);
        $this->cache->setSuffix('');
    }

    /**
     * Returns the current sfCache implementation instance.
     *
     * @return sfCache A sfCache implementation instance
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Generates classes and templates for a given generator class.
     *
     * @param string The generator class name
     * @param array  An array of parameters
     *
     * @return string The cache for the configuration file
     */
    public function generate($generator_class, $param)
    {
        $generator = new $generator_class();
        $generator->initialize($this);
        $data = $generator->generate($param);

        return $data;
    }
}
