<?php

/**
 * Supports the <param> nested tag for PhpTask.
 */
class FunctionParam {

    private $val;

    public function setValue($v) {
        $this->val = $v;
    }

    public function addText($v) {
        $this->val = $v;
    }

    public function getValue() {
        return $this->val;
    }
}
