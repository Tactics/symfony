<?php

require_once 'creole/IdGenerator.php';

/**
 * SQLite IdGenerator implimenation.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.4 $
 * @package   creole.drivers.sqlite
 */
class SQLiteIdGenerator implements IdGenerator {
    
    /**
     * Creates a new IdGenerator class, saves passed connection for use
     * later by getId() method.
     * @param Connection $conn
     */
    public function __construct(private readonly Connection $conn)
    {
    }
    
    /**
     * @see IdGenerator::isBeforeInsert()
     */
    public function isBeforeInsert()
    {
        return false;
    }    
    
    /**
     * @see IdGenerator::isAfterInsert()
     */
    public function isAfterInsert()
    {
        return true;
    }
       
    /**
     * @see IdGenerator::getIdMethod()
     */
    public function getIdMethod()
    {
        return self::AUTOINCREMENT;
    }
    
    /**
     * @see IdGenerator::getId()
     */
    public function getId($unused = null)
    {
        return sqlite_last_insert_rowid($this->conn->getResource());
    }
    
}

