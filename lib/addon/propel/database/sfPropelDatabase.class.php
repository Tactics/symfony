<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A symfony database driver for Propel, derived from the native Creole driver.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>datasource</b>     - [symfony] - datasource to use for the connection
 * # <b>is_default</b>     - [false]   - use as default if multiple connections
 *                                       are specified. The parameters
 *                                       that has been flagged using this param
 *                                       is be used when Propel is initialized
 *                                       via sfPropelAutoload.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfPropelDatabase.class.php 8699 2008-04-30 16:40:41Z FabianLange $
 */
class sfPropelDatabase extends sfCreoleDatabase
{
    protected static $config = [];

    public function initialize($parameters = null, $name = 'propel')
    {
        parent::initialize($parameters);

        if (!$this->hasParameter('datasource')) {
            $this->setParameter('datasource', $name);
        }

        $this->addConfig();

        $is_default = $this->getParameter('is_default', false);

        // first defined if none listed as default
        if ($is_default || count(self::$config['propel']['datasources']) == 1) {
            $this->setDefaultConfig();
        }

        // Add debug database connections
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            // register debug driver
            Creole::registerDriver('*', 'symfony.addon.creole.drivers.sfDebugConnection');

            // register our logger
            sfDebugConnection::setLogger(sfLogger::getInstance());
        }
    }

    public function setDefaultConfig()
    {
        self::$config['propel']['datasources']['default'] = $this->getParameter('datasource');
    }

    public function addConfig()
    {
        if ($this->hasParameter('host')) {
            $this->setParameter('hostspec', $this->getParameter('host'));
        }

        if ($dsn = $this->getParameter('dsn')) {
            $params = Creole::parseDSN($dsn);

            $options = ['phptype', 'hostspec', 'database', 'username', 'password', 'port', 'protocol', 'encoding', 'persistent', 'socket', 'compat_assoc_lower', 'compat_rtrim_string', 'encrypt', 'trust_server_certificate', 'trust_store', 'trust_store_password'];

            foreach ($options as $option) {
                if (!$this->getParameter($option) && isset($params[$option])) {
                    $this->setParameter($option, $params[$option]);
                }
            }
        }

        self::$config['propel']['datasources'][$this->getParameter('datasource')] =
            ['adapter' => $this->getParameter('phptype'), 'connection' => ['phptype' => $this->getParameter('phptype'), 'hostspec' => $this->getParameter('hostspec'), 'database' => $this->getParameter('database'), 'username' => $this->getParameter('username'), 'password' => $this->getParameter('password'), 'port' => $this->getParameter('port'), 'encoding' => $this->getParameter('encoding'), 'persistent' => $this->getParameter('persistent'), 'protocol' => $this->getParameter('protocol'), 'socket' => $this->getParameter('socket'), 'compat_assoc_lower' => $this->getParameter('compat_assoc_lower'), 'compat_rtrim_string' => $this->getParameter('compat_rtrim_string'), 'encrypt' => $this->getParameter('encrypt'), 'trust_server_certificate' => $this->getParameter('trust_server_certificate'), 'trust_store' => $this->getParameter('trust_store'), 'trust_store_password' => $this->getParameter('trust_store_password')]];
    }

    public static function getConfiguration()
    {
        return self::$config;
    }

    public function setConnectionParameter($key, $value)
    {
        if ($key == 'host') {
            $key = 'hostspec';
        }

        self::$config['propel']['datasources'][$this->getParameter('datasource')]['connection'][$key] = $value;
        $this->setParameter($key, $value);
    }
}
