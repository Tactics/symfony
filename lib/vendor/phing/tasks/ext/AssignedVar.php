<?php

/**
 * An "inner" class for holding assigned var values.
 * May be need to expand beyond name/value in the future.
 */
class AssignedVar {

    private $name;
    private $value;

    function setName($v) {
        $this->name = $v;
    }

    function setValue($v) {
        $this->value = $v;
    }

    function getName() {
        return $this->name;
    }

    function getValue() {
        return $this->value;
    }

}
