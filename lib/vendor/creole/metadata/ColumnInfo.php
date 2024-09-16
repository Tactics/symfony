<?php

/*
 *  $Id: ColumnInfo.php,v 1.13 2005/02/25 15:47:02 pachanga Exp $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */

/**
 * Represents a Column.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.13 $
 * @package   creole.metadata
 */
class ColumnInfo {

     /** Table */
    public $table;

    /**
     * Additional and optional vendor specific information.
     * @var vendorSpecificInfo
     */
    protected $vendorSpecificInfo = [];

    /**
     * Construct a new ColumnInfo object.
     *
     * @param TableInfo $table The table that owns this column.
     * @param string $name Column name.
     * @param int $type Creole type.
     * @param string $nativeType Native type name.
     * @param int $size Column length.
     * @param int $scale Column scale (number of digits after decimal).
     * @param boolean $isNullable Whether col is nullable.
     * @param mixed $defaultValue Default value.
     * @param boolean $isAutoIncrement Whether col is of autoIncrement type.
     */
    function __construct(TableInfo
                         $table,
                         public $name,
                         public $type = null,
                         public $nativeType = null,
                         public $size = null,
                         /** Column presision */
                         public $precision=null,
                         public $scale = null,
                         public $isNullable = null,
                         public mixed $defaultValue = null,
                         public $isAutoIncrement = null,
                         $vendorInfo = [])
    {
        $this->table = $table;
        $this->vendorSpecificInfo = $vendorInfo;
    }

    /**
     * This "magic" method is invoked upon serialize().
     * Because the Info class hierarchy is recursive, we must handle
     * the serialization and unserialization of this object.
     * @return array The class variables that should be serialized (all must be public!).
     */
    function __sleep()
    {
        return ['name', 'type', 'nativeType', 'size', 'precision', 'isNullable', 'defaultValue'];
    }

    /**
     * Get column name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get column type.
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the native type name.
     * @return string
     */
    public function getNativeType()
    {
        return $this->nativeType;
    }

    /**
     * Get column size.
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get column precision.
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Get column scale.
     * Scale refers to number of digits after the decimal.  Sometimes this is referred
     * to as precision, but precision is the total number of digits (i.e. length).
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Get the default value.
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Is column nullable?
     * @return boolean
     */
    public function isNullable()
    {
        return $this->isNullable;
    }

    /**
     * Is column of autoincrement type?
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return $this->isAutoIncrement === true;
    }

    /**
     * Get vendor specific optional information for this column.
     * @return array vendorSpecificInfo[]
     */
    public function getVendorSpecificInfo()
    {
        return $this->vendorSpecificInfo;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->name;
    }

    /**
     * Get parent table.
     * @return TableInfo
     */
    public function getTable()
    {
        return $this->table;
    }

}
