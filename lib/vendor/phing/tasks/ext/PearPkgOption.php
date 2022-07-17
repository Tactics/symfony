<?php

/**
 * Generic option class is used for non-complex options.
 */
class PearPkgOption {

    private    $name;
    private $value;

    public function setName($v) { $this->name = $v; }
    public function getName() { return $this->name; }

    public function setValue($v) { $this->value = $v; }
    public function getValue() { return $this->value; }
    public function addText($txt) { $this->value = trim($txt); }

}
