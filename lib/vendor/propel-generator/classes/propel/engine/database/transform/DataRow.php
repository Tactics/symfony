<?php

/**
 * "inner class"
 * @package    propel.engine.database.transform
 */
class DataRow
{
    public function __construct(private readonly Table $table, private $columnValues)
    {
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getColumnValues()
    {
        return $this->columnValues;
    }
}
