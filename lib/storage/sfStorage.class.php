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
 * sfStorage allows you to customize the way symfony stores its persistent data.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfStorage.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
abstract class sfStorage
{
    protected $parameterHolder;
    protected $context;

    /**
     * Retrieves the current application context.
     *
     * @return sfContext A sfContext instance
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Initializes this Storage instance.
     *
     * @param sfContext A sfContext instance
     * @param array   An associative array of initialization parameters
     *
     * @return bool true, if initialization completes successfully, otherwise false
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfStorage
     */
    public function initialize($context, $parameters = [])
    {
        $this->context = $context;

        $this->parameterHolder = new sfParameterHolder();
        $this->getParameterHolder()->add($parameters);
    }

    /**
     * Retrieves a new Storage implementation instance.
     *
     * @param string A Storage implementation name
     *
     * @return Storage A Storage implementation instance
     *
     * @throws <b>sfFactoryException</b> If a storage implementation instance cannot be created
     */
    public static function newInstance($class)
    {
        // the class exists
        $object = new $class();

        if (!($object instanceof sfStorage)) {
            // the class name is of the wrong type
            $error = 'Class "%s" is not of the type sfStorage';
            $error = sprintf($error, $class);

            throw new sfFactoryException($error);
        }

        return $object;
    }

    /**
     * Reads data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string A unique key identifying your data
     *
     * @return mixed Data associated with the key
     *
     * @throws <b>sfStorageException</b> If an error occurs while reading data from this storage
     */
    abstract public function &read($key);

    /**
     * Removes data from this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string A unique key identifying your data
     *
     * @return mixed Data associated with the key
     *
     * @throws <b>sfStorageException</b> If an error occurs while removing data from this storage
     */
    abstract public function &remove($key);

    /**
     * Executes the shutdown procedure.
     *
     * @throws <b>sfStorageException</b> If an error occurs while shutting down this storage
     */
    abstract public function shutdown();

    /**
     * Writes data to this storage.
     *
     * The preferred format for a key is directory style so naming conflicts can be avoided.
     *
     * @param string A unique key identifying your data
     * @param mixed  Data associated with your key
     *
     * @throws <b>sfStorageException</b> If an error occurs while writing to this storage
     */
    abstract public function write($key, &$data);

    /**
     * Retrieves the parameters from the storage.
     *
     * @return sfParameterHolder List of parameters
     */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }

    /**
     * Retrieves a parameter from the validator.
     *
     * @param string Parameter name
     * @param mixed A default parameter
     * @param string Namespace for the current storage
     *
     * @return mixed A parameter value
     */
    public function getParameter($name, $default = null, $ns = null)
    {
        return $this->parameterHolder->get($name, $default, $ns);
    }

    /**
     * Indicates whether or not a parameter exist for the storage instance.
     *
     * @param string A parameter name
     * @param string A parameter namespace
     *
     * @return bool true, if parameter exists, otherwise false
     */
    public function hasParameter($name, $ns = null)
    {
        return $this->parameterHolder->has($name, $ns);
    }

    /**
     * Sets a parameter for the current storage instance.
     *
     * @param string A parameter name
     * @param mixed A parameter value
     * @param string Namespace for the current storage
     */
    public function setParameter($name, $value, $ns = null)
    {
        return $this->parameterHolder->set($name, $value, $ns);
    }
}
