<?php


/**
 * "inner" class
 * @package    propel.engine.database.transform
 */
class ColumnValue {

    public function __construct(private readonly Column $col, private $val)
    {
    }

    public function getColumn()
    {
        return $this->col;
    }

    public function getValue()
    {
        return $this->val;
    }
}
