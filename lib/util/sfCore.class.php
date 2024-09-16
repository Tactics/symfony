<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * core symfony class.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfCore.class.php 9893 2008-06-26 12:12:48Z fabien $
 */
class sfCore
{
    protected static $autoloadCallables = [];
    protected static $classes = [];

    public static function bootstrap($sf_symfony_lib_dir, $sf_symfony_data_dir)
    {
        require_once $sf_symfony_lib_dir.'/util/sfToolkit.class.php';
        require_once $sf_symfony_lib_dir.'/config/sfConfig.class.php';

        sfCore::initConfiguration($sf_symfony_lib_dir, $sf_symfony_data_dir);

        sfCore::initIncludePath();

        sfCore::callBootstrap();

        if (sfConfig::get('sf_check_lock')) {
            sfCore::checkLock();
        }
        if (sfConfig::get('sf_check_symfony_version')) {
            sfCore::checkSymfonyVersion();
        }
    }

    public static function callBootstrap()
    {
        $bootstrap = sfConfig::get('sf_config_cache_dir').'/config_bootstrap_compile.yml.php';
        if (is_readable($bootstrap)) {
            sfConfig::set('sf_in_bootstrap', true);
            require $bootstrap;
        } else {
            require sfConfig::get('sf_symfony_lib_dir').'/symfony.php';
        }
    }

    public static function initConfiguration($sf_symfony_lib_dir, $sf_symfony_data_dir, $test = false)
    {
        // start timer
        if (SF_DEBUG) {
            sfConfig::set('sf_timer_start', microtime(true));
        }

        // main configuration
        sfConfig::add(['sf_root_dir' => SF_ROOT_DIR, 'sf_app' => SF_APP, 'sf_environment' => SF_ENVIRONMENT, 'sf_debug' => SF_DEBUG, 'sf_symfony_lib_dir' => $sf_symfony_lib_dir, 'sf_symfony_data_dir' => $sf_symfony_data_dir, 'sf_test' => $test]);

        // directory layout
        include $sf_symfony_data_dir.'/config/constants.php';
    }

    public static function initIncludePath()
    {
        set_include_path(
            sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
            sfConfig::get('sf_root_dir').PATH_SEPARATOR.
            sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
            sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
            get_include_path()
        );
    }

    // check to see if we're not in a cache cleaning process
    public static function checkLock()
    {
        if (
            sfToolkit::hasLockFile(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'-cli.lck', 5)
            || sfToolkit::hasLockFile(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.lck')
        ) {
            // application is not available
            $file = sfConfig::get('sf_web_dir').'/errors/unavailable.php';
            include is_readable($file) ? $file : sfConfig::get('sf_symfony_data_dir').'/web/errors/unavailable.php';

            exit(1);
        }
    }

    public static function checkSymfonyVersion()
    {
        // recent symfony update?
        $last_version = @file_get_contents(sfConfig::get('sf_config_cache_dir').'/VERSION');
        $current_version = trim(file_get_contents(sfConfig::get('sf_symfony_lib_dir').'/VERSION'));
        if ($last_version != $current_version) {
            // clear cache
            sfToolkit::clearDirectory(sfConfig::get('sf_config_cache_dir'));
        }
    }

    public static function getClassPath($class)
    {
        return self::$classes[$class] ?? null;
    }
}
