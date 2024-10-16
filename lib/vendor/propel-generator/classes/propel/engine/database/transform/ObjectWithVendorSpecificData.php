<?php

/**
 * Utility class used for objects with vendor data.
 *
 * @package    propel.engine.database.transform
 */
class ObjectWithVendorSpecificData
{
    public function __construct(protected $object, protected $vendorType)
    {
    }

    public function isCompatible($type)
    {
        return ($this->vendorType == $type);
    }

    public function setVendorParameter($name, $value)
    {
        $this->object->setVendorParameter($name, $value);
    }
}
