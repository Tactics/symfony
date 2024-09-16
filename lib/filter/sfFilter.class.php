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
 * sfFilter provides a way for you to intercept incoming requests or outgoing responses.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfFilter.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
abstract class sfFilter
{
    protected $parameterHolder;
    protected $context;

    public static $filterCalled = [];

    /**
     * Returns true if this is the first call to the sfFilter instance.
     *
     * @return bool true if this is the first call to the sfFilter instance, false otherwise
     */
    protected function isFirstCall()
    {
        $class = static::class;
        if (isset(self::$filterCalled[$class])) {
            return false;
        } else {
            self::$filterCalled[$class] = true;

            return true;
        }
    }

    /**
     * Retrieves the current application context.
     *
     * @return sfContext The current sfContext instance
     */
    final public function getContext()
    {
        return $this->context;
    }

    /**
     * Initializes this Filter.
     *
     * @param sfContext The current application context
     * @param array   An associative array of initialization parameters
     *
     * @return bool true, if initialization completes successfully, otherwise false
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this Filter
     */
    public function initialize($context, $parameters = [])
    {
        $this->context = $context;

        $this->parameterHolder = new sfParameterHolder();
        $this->parameterHolder->add($parameters);

        return true;
    }

    /**
     * Gets the parameter holder for this object.
     *
     * @return sfParameterHolder A sfParameterHolder instance
     */
    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }

    /**
     * Gets the parameter associated with the given key.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->get()</code>
     *
     * @param string The key name
     * @param string The default value
     * @param string The namespace to use
     *
     * @return string The value associated with the key
     *
     * @see sfParameterHolder
     */
    public function getParameter($name, $default = null, $ns = null)
    {
        return $this->parameterHolder->get($name, $default, $ns);
    }

    /**
     * Returns true if the given key exists in the parameter holder.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->has()</code>
     *
     * @param string The key name
     * @param string The namespace to use
     *
     * @return bool true if the given key exists, false otherwise
     *
     * @see sfParameterHolder
     */
    public function hasParameter($name, $ns = null)
    {
        return $this->parameterHolder->has($name, $ns);
    }

    /**
     * Sets the value for the given key.
     *
     * This is a shortcut for:
     *
     * <code>$this->getParameterHolder()->set()</code>
     *
     * @param string The key name
     * @param string The value
     * @param string The namespace to use
     *
     * @see sfParameterHolder
     */
    public function setParameter($name, $value, $ns = null)
    {
        return $this->parameterHolder->set($name, $value, $ns);
    }
}
