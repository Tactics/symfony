<?php

/**
 * Admin generator column.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfAdminGenerator.class.php 9861 2008-06-25 12:07:06Z fabien $
 */
class sfAdminColumn
{
    protected $flags = [];

    /**
     * Constructor.
     *
     * @param string The column php name
     * @param string The column name
     * @param array  The column flags
     */
    public function __construct(protected $phpName, protected $column = null, $flags = [])
    {
        $this->flags = (array) $flags;
    }

    /**
     * Returns true if the column maps a database column.
     *
     * @return bool true if the column maps a database column, false otherwise
     */
    public function isReal()
    {
        return $this->column ? true : false;
    }

    /**
     * Gets the name of the column.
     *
     * @return string The column name
     */
    public function getName()
    {
        return sfInflector::underscore($this->phpName);
    }

    /**
     * Returns true if the column is a partial.
     *
     * @return bool true if the column is a partial, false otherwise
     */
    public function isPartial()
    {
        return in_array('_', $this->flags) ? true : false;
    }

    /**
     * Returns true if the column is a component.
     *
     * @return bool true if the column is a component, false otherwise
     */
    public function isComponent()
    {
        return in_array('~', $this->flags) ? true : false;
    }

    /**
     * Returns true if the column has a link.
     *
     * @return bool true if the column has a link, false otherwise
     */
    public function isLink()
    {
        return (in_array('=', $this->flags) || $this->isPrimaryKey()) ? true : false;
    }

    /**
     * Gets the php name of the column.
     *
     * @return string The php name
     */
    public function getPhpName()
    {
        return $this->phpName;
    }

    // FIXME: those methods are only used in the propel admin generator
    public function __call($name, $arguments)
    {
        return $this->column ? $this->column->$name() : null;
    }
}
