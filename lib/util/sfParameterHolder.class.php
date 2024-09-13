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
 * sfParameterHolder provides a base class for managing parameters.
 *
 * Parameters, in this case, are used to extend classes with additional data
 * that requires no additional logic to manage.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfParameterHolder.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfParameterHolder
{
    protected $parameters = [];

    /**
     * The constructor for sfParameterHolder.
     *
     * The default namespace may be overridden at initialization as follows:
     * <code>
     * <?php
     * $mySpecialPH = new sfParameterHolder('symfony/special');
     * ?>
     * </code>
     */
    public function __construct(protected $default_namespace = 'symfony/default')
    {
    }

    /**
     * Get the default namespace value.
     *
     * The $default_namespace is defined as 'symfony/default'.
     *
     * @return string the default namespace
     */
    public function getDefaultNamespace()
    {
        return $this->default_namespace;
    }

    /**
     * Clear all parameters associated with this request.
     *
     * @return void
     */
    public function clear()
    {
        $this->parameters = null;
        $this->parameters = [];
    }

    /**
     * Retrieve a parameter with an optionally specified namespace.
     *
     * An isolated namespace may be identified by providing a value for the third
     * argument.  If not specified, the default namespace 'symfony/default' is
     * used.
     *
     * @param string a parameter name
     * @param mixed  a default parameter value
     * @param string a parameter namespace
     *
     * @return mixed a parameter value, if the parameter exists, otherwise null
     */
    public function &get($name, $default = null, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (isset($this->parameters[$ns][$name])) {
            $value = &$this->parameters[$ns][$name];
        } elseif (isset($this->parameters[$ns])) {
            $value = sfToolkit::getArrayValueForPath($this->parameters[$ns], $name, $default);
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Retrieve an array of parameter names from an optionally specified namespace.
     *
     * @param string a parameter namespace
     *
     * @return array an indexed array of parameter names, if the namespace exists, otherwise null
     */
    public function getNames($ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (isset($this->parameters[$ns])) {
            return array_keys($this->parameters[$ns]);
        }

        return [];
    }

    /**
     * Retrieve an array of parameter namespaces.
     *
     * @return array an indexed array of parameter namespaces
     */
    public function getNamespaces()
    {
        return array_keys($this->parameters);
    }

    /**
     * Retrieve an array of parameters, within a namespace.
     *
     * This method is limited to a namespace.  Without any argument,
     * it returns the parameters of the default namespace.  If a
     * namespace is passed as an argument, only the parameters of the
     * specified namespace are returned.
     *
     * @param string a parameter namespace
     *
     * @return array an associative array of parameters
     */
    public function &getAll($ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        $parameters = [];

        if (isset($this->parameters[$ns])) {
            $parameters = $this->parameters[$ns];
        }

        return $parameters;
    }

    /**
     * Indicates whether or not a parameter exists.
     *
     * @param string a parameter name
     * @param string a parameter namespace
     *
     * @return bool true, if the parameter exists, otherwise false
     */
    public function has($name, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (false !== ($offset = strpos((string) $name, '['))) {
            if (isset($this->parameters[$ns][substr((string) $name, 0, $offset)])) {
                $array = $this->parameters[$ns][substr((string) $name, 0, $offset)];

                while ($pos = strpos((string) $name, '[', $offset)) {
                    $end = strpos((string) $name, ']', $pos);
                    if ($end == $pos + 1) {
                        // reached a []
                        return true;
                    } elseif (!isset($array[substr((string) $name, $pos + 1, $end - $pos - 1)])) {
                        return false;
                    }
                    $array = $array[substr((string) $name, $pos + 1, $end - $pos - 1)];
                    $offset = $end;
                }

                return true;
            }
        } elseif (isset($this->parameters[$ns][$name])) {
            return true;
        }

        return false;
    }

    /**
     * Indicates whether or not A parameter namespace exists.
     *
     * @param string a parameter namespace
     *
     * @return bool true, if the namespace exists, otherwise false
     */
    public function hasNamespace($ns)
    {
        return isset($this->parameters[$ns]);
    }

    /**
     * Remove a parameter.
     *
     * @param string a parameter name
     * @param string a parameter namespace
     *
     * @return string a parameter value, if the parameter was removed, otherwise null
     */
    public function &remove($name, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        $retval = null;

        if (isset($this->parameters[$ns]) && isset($this->parameters[$ns][$name])) {
            $retval = &$this->parameters[$ns][$name];
            unset($this->parameters[$ns][$name]);
        }

        return $retval;
    }

    /**
     * Remove A parameter namespace and all of its associated parameters.
     *
     * @param string a parameter namespace
     *
     * @return void
     */
    public function &removeNamespace($ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        $retval = null;

        if (isset($this->parameters[$ns])) {
            $retval = &$this->parameters[$ns];
            unset($this->parameters[$ns]);
        }

        return $retval;
    }

    /**
     * Set a parameter.
     *
     * If a parameter with the name already exists the value will be overridden.
     *
     * @param string a parameter name
     * @param mixed  a parameter value
     * @param string a parameter namespace
     *
     * @return void
     */
    public function set($name, $value, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (!isset($this->parameters[$ns])) {
            $this->parameters[$ns] = [];
        }

        $this->parameters[$ns][$name] = $value;
    }

    /**
     * Set a parameter by reference.
     *
     * If a parameter with the name already exists the value will be overridden.
     *
     * @param string a parameter name
     * @param mixed  a reference to a parameter value
     * @param string a parameter namespace
     *
     * @return void
     */
    public function setByRef($name, &$value, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (!isset($this->parameters[$ns])) {
            $this->parameters[$ns] = [];
        }

        $this->parameters[$ns][$name] = &$value;
    }

    /**
     * Set an array of parameters.
     *
     * If an existing parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @param array an associative array of parameters and their associated values
     * @param string a parameter namespace
     *
     * @return void
     */
    public function add($parameters, $ns = null)
    {
        if ($parameters === null) {
            return;
        }

        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (!isset($this->parameters[$ns])) {
            $this->parameters[$ns] = [];
        }

        foreach ($parameters as $key => $value) {
            $this->parameters[$ns][$key] = $value;
        }
    }

    /**
     * Set an array of parameters by reference.
     *
     * If an existing parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @param array an associative array of parameters and references to their associated values
     * @param string a parameter namespace
     *
     * @return void
     */
    public function addByRef(&$parameters, $ns = null)
    {
        if (!$ns) {
            $ns = $this->default_namespace;
        }

        if (!isset($this->parameters[$ns])) {
            $this->parameters[$ns] = [];
        }

        foreach ($parameters as $key => &$value) {
            $this->parameters[$ns][$key] = &$value;
        }
    }
}
