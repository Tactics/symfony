<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfigHandler allows a developer to create a custom formatted configuration
 * file pertaining to any information they like and still have it auto-generate
 * PHP code.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfConfigHandler.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
abstract class sfConfigHandler
{
    protected $parameterHolder;

    /**
     * Executes this configuration handler.
     *
     * @param array An array of filesystem path to a configuration file
     *
     * @return string Data to be written to a cache file
     *
     * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist or is not readable
     * @throws <b>sfParseException</b> If a requested configuration file is improperly formatted
     */
    abstract public function execute($configFiles);

    /**
     * Initializes this configuration handler.
     *
     * @param array An associative array of initialization parameters
     *
     * @return bool true, if initialization completes successfully, otherwise false
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this ConfigHandler
     */
    public function initialize($parameters = null)
    {
        $this->parameterHolder = new sfParameterHolder();
        $this->parameterHolder->add($parameters);
    }

    /**
     * Replaces constant identifiers in a value.
     *
     * If the value is an array replacements are made recursively.
     *
     * @param mixed The value on which to run the replacement procedure
     *
     * @return string The new value
     */
    public static function replaceConstants($value)
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$value) {
                $value = sfToolkit::replaceConstants($value);
            });
        } else {
            $value = sfToolkit::replaceConstants($value);
        }

        return $value;
    }

    /**
     * Replaces a relative filesystem path with an absolute one.
     *
     * @param string A relative filesystem path
     *
     * @return string The new path
     */
    public static function replacePath($path)
    {
        if (!sfToolkit::isPathAbsolute($path)) {
            // not an absolute path so we'll prepend to it
            $path = sfConfig::get('sf_app_dir').'/'.$path;
        }

        return $path;
    }

    /**
     * Gets the parameter holder for this configuration handler.
     *
     * @return sfParameterHolder A sfParameterHolder instance
     */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }
}
