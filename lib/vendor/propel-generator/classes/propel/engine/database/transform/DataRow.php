<?php

/**
 * "inner class"
 * @package    propel.engine.database.transform
 */
class DataRow
{
    private $table;
    private $columnValues;

    public function __construct(Table $table, $columnValues)
    {
        $this->table = $table;
        $this->columnValues = $columnValues;
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
