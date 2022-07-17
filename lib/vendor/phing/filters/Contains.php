<?php

/**
 * Holds a contains element.
 */
class Contains {

    /**
     * @var string
     */
    private $_value;

    /**
     * Set 'contains' value.
     * @param string $contains
     */
    function setValue($contains) {
        $this->_value = (string) $contains;
    }

    /**
     * Returns 'contains' value.
     * @return string
     */
    function getValue() {
        return $this->_value;
    }
}
