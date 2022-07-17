<?php


/**
 * "inner" class
 * @package    propel.engine.database.transform
 */
class ColumnValue {

    private $col;
    private $val;

    public function __construct(Column $col, $val)
    {
        $this->col = $col;
        $this->val = $val;
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
