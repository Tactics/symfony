<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCacheConfigHandler allows you to configure cache.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfCacheConfigHandler.class.php 3203 2007-01-09 18:32:54Z fabien $
 */
class sfCacheConfigHandler extends sfYamlConfigHandler
{
    protected $cacheConfig = [];

    /**
     * Executes this configuration handler.
     *
     * @param array An array of absolute filesystem path to a configuration file
     *
     * @return string Data to be written to a cache file
     *
     * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist or is not readable
     * @throws <b>sfParseException</b> If a requested configuration file is improperly formatted
     * @throws <b>sfInitializationException</b> If a cache.yml key check fails
     */
    public function execute($configFiles)
    {
        // set our required categories list and initialize our handler
        $categories = ['required_categories' => []];
        $this->initialize($categories);

        // parse the yaml
        $myConfig = $this->parseYamls($configFiles);

        $myConfig['all'] = sfToolkit::arrayDeepMerge(
            isset($myConfig['default']) && is_array($myConfig['default']) ? $myConfig['default'] : [],
            isset($myConfig['all']) && is_array($myConfig['all']) ? $myConfig['all'] : []
        );

        unset($myConfig['default']);

        $this->yamlConfig = $myConfig;

        // iterate through all action names
        $data = [];
        $first = true;
        foreach ($this->yamlConfig as $actionName => $values) {
            if ($actionName == 'all') {
                continue;
            }

            $data[] = $this->addCache($actionName);

            $first = false;
        }

        // general cache configuration
        $data[] = $this->addCache('DEFAULT');

        // compile data
        $retval = sprintf("<?php\n".
                          "// auto-generated by sfCacheConfigHandler\n".
                          "// date: %s\n%s\n",
            date('Y/m/d H:i:s'), implode('', $data));

        return $retval;
    }

    /**
     * Returns a single addCache statement.
     *
     * @param string The action name
     *
     * @return string PHP code for the addCache statement
     */
    protected function addCache($actionName = '')
    {
        $data = [];

        // enabled?
        $enabled = $this->getConfigValue('enabled', $actionName);

        // cache with or without loayout
        $withLayout = $this->getConfigValue('with_layout', $actionName) ? 'true' : 'false';

        // lifetime
        $lifeTime = !$enabled ? '0' : $this->getConfigValue('lifetime', $actionName, '0');

        // client_lifetime
        $clientLifetime = !$enabled ? '0' : $this->getConfigValue('client_lifetime', $actionName, $lifeTime);

        // contextual
        $contextual = $this->getConfigValue('contextual', $actionName) ? 'true' : 'false';

        // vary
        $vary = $this->getConfigValue('vary', $actionName, []);
        if (!is_array($vary)) {
            $vary = [$vary];
        }

        // add cache information to cache manager
        $data[] = sprintf("\$this->addCache(\$moduleName, '%s', array('withLayout' => %s, 'lifeTime' => %s, 'clientLifeTime' => %s, 'contextual' => %s, 'vary' => %s));\n",
            $actionName, $withLayout, $lifeTime, $clientLifetime, $contextual, str_replace("\n", '', var_export($vary, true)));

        return implode("\n", $data);
    }
}
