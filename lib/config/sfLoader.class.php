<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoader is a class which contains the logic to look for files/classes in symfony.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfLoader.class.php 10701 2008-08-06 09:44:41Z hartym $
 */
class sfLoader
{
    public static $templatePluginDirs = [];
    public static $templateModuleDirs = [];
    public static $templateFileDirs = [];
    public static $pluginModuleHelperDirs = [];
    public static $pluginGlobalHelperDirs;
    public static $pluginGlobalConfigPaths = [];
    public static $pluginConfigPaths = [];

    /**
     * Gets directories where model classes are stored. The order of returned paths is lowest precedence
     * to highest precedence.
     *
     * @return array An array of directories
     */
    public static function getModelDirs()
    {
        $dirs = []; // project

        if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/lib/model')) {
            $dirs = array_merge($dirs, $pluginDirs);                                                                // plugins
        }

        $dirs[] = sfConfig::get('sf_lib_dir') ? sfConfig::get('sf_lib_dir').'/model' : 'lib/model';

        return $dirs;
    }

    /**
     * Gets directories where controller classes are stored for a given module.
     *
     * @param string The module name
     *
     * @return array An array of directories
     */
    public static function getControllerDirs($moduleName)
    {
        $suffix = $moduleName.'/'.sfConfig::get('sf_app_module_action_dir_name');

        $dirs = [];
        foreach (sfConfig::get('sf_module_dirs', []) as $key => $value) {
            $dirs[$key.'/'.$suffix] = $value;
        }

        $dirs[sfConfig::get('sf_app_module_dir').'/'.$suffix] = false;                                     // application

        if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$suffix)) {
            $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), true))); // plugins
        }

        $dirs[sfConfig::get('sf_symfony_data_dir').'/modules/'.$suffix] = true;                            // core modules

        return $dirs;
    }

    /**
     * Gets directories where template files are stored for a given module.
     *
     * @param string The module name
     *
     * @return array An array of directories
     */
    public static function getTemplateDirs($moduleName)
    {
        $suffix = $moduleName.'/'.sfConfig::get('sf_app_module_template_dir_name');

        $dirs = [];
        foreach (sfConfig::get('sf_module_dirs', []) as $key => $value) {
            $dirs[] = $key.'/'.$suffix;
        }

        $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$suffix;                        // application

        if (!isset(self::$templatePluginDirs[$moduleName]) && extension_loaded('wincache')) {
            self::$templatePluginDirs[$moduleName] = self::getForPathFromUCache('templatePluginDirs.'.$moduleName, sfConfig::get('sf_plugins_dir').'/*/modules/'.$suffix);
        } elseif (!isset(self::$templatePluginDirs[$moduleName])) {
            self::$templatePluginDirs[$moduleName] = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$suffix);
        }

        if ($pluginDirs = self::$templatePluginDirs[$moduleName]) {
            $dirs = array_merge($dirs, $pluginDirs);                                       // plugins
        }

        $dirs[] = sfConfig::get('sf_symfony_data_dir').'/modules/'.$suffix;              // core modules
        $dirs[] = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($suffix);         // generated templates in cache

        return $dirs;
    }

    /**
     * Gets the template directory to use for a given module and template file.
     *
     * @param string The module name
     * @param string The template file
     *
     * @return string A template directory
     */
    public static function getTemplateDir($moduleName, $templateFile)
    {
        if (isset(self::$templateFileDirs[$moduleName][$templateFile])) {
            return self::$templateFileDirs[$moduleName][$templateFile];
        }

        if (!isset(self::$templateModuleDirs[$moduleName])) {
            self::$templateModuleDirs[$moduleName] = self::getTemplateDirs($moduleName);
        }

        $dirs = self::$templateModuleDirs[$moduleName];
        foreach ($dirs as $dir) {
            if (file_exists($dir.'/'.$templateFile) && is_readable($dir.'/'.$templateFile)) {
                self::$templateFileDirs[$moduleName][$templateFile] = $dir;

                return $dir;
            }
        }

        return null;
    }

    /**
     * Gets the template to use for a given module and template file.
     *
     * @param string The module name
     * @param string The template file
     *
     * @return string A template path
     */
    public static function getTemplatePath($moduleName, $templateFile)
    {
        $dir = self::getTemplateDir($moduleName, $templateFile);

        return $dir ? $dir.'/'.$templateFile : null;
    }

    /**
     * Gets the i18n directory to use for a given module.
     *
     * @param string The module name
     *
     * @return string An i18n directory
     */
    public static function getI18NDir($moduleName)
    {
        $suffix = $moduleName.'/'.sfConfig::get('sf_app_module_i18n_dir_name');

        // application
        $dir = sfConfig::get('sf_app_module_dir').'/'.$suffix;
        if (is_dir($dir)) {
            return $dir;
        }

        // plugins
        $dirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$suffix);
        if (isset($dirs[0])) {
            return $dirs[0];
        }
    }

    /**
     * Gets directories where template files are stored for a generator class and a specific theme.
     *
     * @param string The generator class name
     * @param string The theme name
     *
     * @return array An array of directories
     */
    public static function getGeneratorTemplateDirs($class, $theme)
    {
        $dirs = [sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/template'];                  // project

        if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/data/generator/'.$class.'/'.$theme.'/template')) {
            $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
        }

        $dirs[] = sfConfig::get('sf_symfony_data_dir').'/generator/'.$class.'/default/template';                  // default theme

        return $dirs;
    }

    /**
     * Gets directories where the skeleton is stored for a generator class and a specific theme.
     *
     * @param string The generator class name
     * @param string The theme name
     *
     * @return array An array of directories
     */
    public static function getGeneratorSkeletonDirs($class, $theme)
    {
        $dirs = [sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/skeleton'];                  // project

        if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/data/generator/'.$class.'/'.$theme.'/skeleton')) {
            $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
        }

        $dirs[] = sfConfig::get('sf_symfony_data_dir').'/generator/'.$class.'/default/skeleton';                  // default theme

        return $dirs;
    }

    /**
     * Gets the template to use for a generator class.
     *
     * @param string The generator class name
     * @param string The theme name
     * @param string The template path
     *
     * @return string A template path
     *
     * @throws sfException
     */
    public static function getGeneratorTemplate($class, $theme, $path)
    {
        $dirs = self::getGeneratorTemplateDirs($class, $theme);
        foreach ($dirs as $dir) {
            if (is_readable($dir.'/'.$path)) {
                return $dir.'/'.$path;
            }
        }

        throw new sfException(sprintf('Unable to load "%s" generator template in: %s', $path, implode(', ', $dirs)));
    }

    /**
     * Gets the configuration file paths for a given relative configuration path.
     *
     * @param string The configuration path
     *
     * @return array An array of paths
     */
    public static function getConfigPaths($configPath)
    {
        $globalConfigPath = basename(dirname((string) $configPath)).'/'.basename((string) $configPath);

        $files = [
            sfConfig::get('sf_symfony_data_dir').'/'.$globalConfigPath,
            // symfony
            sfConfig::get('sf_symfony_data_dir').'/'.$configPath,
        ];

        if ($pluginDirs = self::getPluginGlobalConfigPaths($globalConfigPath)) {
            $files = array_merge($files, $pluginDirs);                                     // plugins
        }

        $files = array_merge($files, [
            sfConfig::get('sf_root_dir').'/'.$globalConfigPath,
            // project
            sfConfig::get('sf_root_dir').'/'.$configPath,
            // project
            sfConfig::get('sf_app_dir').'/'.$globalConfigPath,
            // application
            sfConfig::get('sf_cache_dir').'/'.$configPath,
        ]);

        if ($pluginDirs = self::getPluginConfigPaths($configPath)) {
            $files = array_merge($files, $pluginDirs);                                     // plugins
        }

        $files[] = sfConfig::get('sf_app_dir').'/'.$configPath;                          // module

        $configs = [];
        foreach (array_unique($files) as $file) {
            if (is_readable($file)) {
                $configs[] = $file;
            }
        }

        return $configs;
    }

    /**
     * Gets the helper directories for a given module name.
     *
     * @param string The module name
     *
     * @return array An array of directories
     */
    public static function getHelperDirs($moduleName = '')
    {
        $dirs = [];

        if ($moduleName) {
            $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_lib_dir_name').'/helper'; // module

            if ($pluginDirs = self::getPluginModuleHelperDirs($moduleName)) {
                $dirs = array_merge($dirs, $pluginDirs);                                                                              // module plugins
            }
        }

        $dirs[] = sfConfig::get('sf_app_lib_dir').'/helper';                                                                      // application

        $dirs[] = sfConfig::get('sf_lib_dir').'/helper';                                                                          // project

        if ($pluginDirs = self::getPluginGlobalHelperDirs()) {
            $dirs = array_merge($dirs, $pluginDirs);                                                                                // plugins
        }

        $dirs[] = sfConfig::get('sf_symfony_lib_dir').'/helper';                                                                  // global

        return $dirs;
    }

    /**
     * Loads helpers.
     *
     * @param array  An array of helpers to load
     * @param string A module name (optional)
     *
     * @throws sfViewException
     */
    public static function loadHelpers($helpers, $moduleName = '')
    {
        static $loaded = [];

        $dirs = self::getHelperDirs($moduleName);
        foreach ((array) $helpers as $helperName) {
            if (isset($loaded[$helperName])) {
                continue;
            }

            $fileName = $helperName.'Helper.php';
            foreach ($dirs as $dir) {
                $included = false;
                if (is_readable($dir.'/'.$fileName)) {
                    include $dir.'/'.$fileName;
                    $included = true;
                    break;
                }
            }

            if (!$included) {
                // search in the include path
                if ((@include ('helper/'.$fileName)) != 1) {
                    $dirs = array_merge($dirs, explode(PATH_SEPARATOR, get_include_path()));

                    // remove sf_root_dir from dirs
                    foreach ($dirs as &$dir) {
                        $dir = str_replace('%SF_ROOT_DIR%', sfConfig::get('sf_root_dir'), $dir);
                    }

                    throw new sfViewException(sprintf('Unable to load "%sHelper.php" helper in: %s', $helperName, implode(', ', $dirs)));
                }
            }

            $loaded[$helperName] = true;
        }
    }

    public static function loadPluginConfig()
    {
        if ($pluginConfigs = glob(sfConfig::get('sf_plugins_dir').'/*/config/config.php')) {
            foreach ($pluginConfigs as $config) {
                include $config;
            }
        }
    }

    /**
     * @param string $moduleName
     *
     * @return array
     */
    private static function getPluginModuleHelperDirs($moduleName)
    {
        if (extension_loaded('wincache')) {
            $dirs = self::getForPathFromUCache('pluginModuleHelperDirs.'.$moduleName, sfConfig::get('sf_plugins_dir').'/*/modules/'.$moduleName.'/lib/helper');

            return $dirs;
        }

        if (!isset(self::$pluginModuleHelperDirs[$moduleName])) {
            $dirs = glob(sfConfig::get('sf_plugins_dir').'/*/modules/'.$moduleName.'/lib/helper');
            self::$pluginModuleHelperDirs[$moduleName] = $dirs;
        }

        return self::$pluginModuleHelperDirs[$moduleName];
    }

    /**
     * @return array
     */
    private static function getPluginGlobalHelperDirs()
    {
        if (extension_loaded('wincache')) {
            $dirs = self::getForPathFromUCache('pluginGlobalHelperDirs', sfConfig::get('sf_plugins_dir').'/*/lib/helper');

            return $dirs;
        }

        if (!self::$pluginGlobalHelperDirs) {
            $dirs = glob(sfConfig::get('sf_plugins_dir').'/*/lib/helper');
            self::$pluginGlobalHelperDirs = $dirs;
        }

        return self::$pluginGlobalHelperDirs;
    }

    /**
     * @param string $globalConfigPath
     *
     * @return array
     */
    private static function getPluginGlobalConfigPaths($globalConfigPath)
    {
        if (extension_loaded('wincache')) {
            $paths = self::getForPathFromUCache('pluginGlobalConfigDirs.'.$globalConfigPath, sfConfig::get('sf_plugins_dir').'/*/'.$globalConfigPath);

            return $paths;
        }

        if (!isset(self::$pluginGlobalConfigPaths[$globalConfigPath])) {
            $paths = glob(sfConfig::get('sf_plugins_dir').'/*/'.$globalConfigPath);
            self::$pluginGlobalConfigPaths[$globalConfigPath] = $paths;
        }

        return self::$pluginGlobalConfigPaths[$globalConfigPath];
    }

    /**
     * @param string $configPath
     *
     * @return array
     */
    private static function getPluginConfigPaths($configPath)
    {
        if (extension_loaded('wincache')) {
            $dirs = self::getForPathFromUCache('pluginConfigDirs.'.$configPath, sfConfig::get('sf_plugins_dir').'/*/'.$configPath);

            return $dirs;
        }

        if (!isset(self::$pluginConfigPaths[$configPath])) {
            $dirs = glob(sfConfig::get('sf_plugins_dir').'/*/'.$configPath);
            self::$pluginConfigPaths[$configPath] = $dirs;
        }

        return self::$pluginConfigPaths[$configPath];
    }

    /**
     * @return array
     */
    private static function getForPathFromUCache($key, $path)
    {
        $inUCache = false;
        $value = wincache_ucache_get($key, $inUCache);
        if (!$inUCache) {
            $value = glob($path);
            wincache_ucache_add($key, $value);
        }

        return $value;
    }
}
