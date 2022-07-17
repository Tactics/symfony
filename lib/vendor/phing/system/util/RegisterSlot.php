<?php


/**
 * Represents a slot in the register.
 */
class RegisterSlot {

    /** The name of this slot. */
    private $key;

    /** The value for this slot. */
    private $value;

    /**
     * Constructs a new RegisterSlot, setting the key to passed param.
     * @param string $key
     */
    public function __construct($key) {
        $this->key = (string) $key;
    }

    /**
     * Sets the key / name for this slot.
     * @param string $k
     */
    public function setKey($k) {
        $this->key = (string) $k;
    }

    /**
     * Gets the key / name for this slot.
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Sets the value for this slot.
     * @param mixed
     */
    public function setValue($v) {
        $this->value = $v;
    }

    /**
     * Returns the value at this slot.
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

}
