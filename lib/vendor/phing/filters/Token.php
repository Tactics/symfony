<?php


/**
 * Holds a token.
 */
class Token {

    /**
     * Token key.
     * @var string
     */
    private $_key;

    /**
     * Token value.
     * @var string
     */
    private $_value;

    /**
     * Sets the token key.
     *
     * @param string $key The key for this token. Must not be <code>null</code>.
     */
    function setKey($key) {
        $this->_key = (string) $key;
    }

    /**
     * Sets the token value.
     *
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    function setValue($value) {
        $this->_value = (string) $value;
    }

    /**
     * Returns the key for this token.
     *
     * @return string The key for this token.
     */
    function getKey() {
        return $this->_key;
    }

    /**
     * Returns the value for this token.
     *
     * @return string The value for this token.
     */
    function getValue() {
        return $this->_value;
    }
}
