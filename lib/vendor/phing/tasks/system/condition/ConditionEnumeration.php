<?php


/**
 * "Inner" class for handling enumerations.
 * Uses build-in PHP5 iterator support.
 */
class ConditionEnumeration implements Iterator {

    /** Current element number */
    private int $num = 0;

    public function __construct(
        /** "Outer" ConditionBase class. */
        private readonly ConditionBase $outer
    )
    {
    }

    public function valid(): bool
    {
        return $this->outer->countConditions() > $this->num;
    }

    public function current() : mixed {
        $o = $this->outer->conditions[$this->num];
        if ($o instanceof ProjectComponent) {
            $o->setProject($this->outer->getProject());
        }
        return $o;
    }

    public function next() : void {
        $this->num++;
    }

    public function key() : mixed {
        return $this->num;
    }

    public function rewind() : void {
        $this->num = 0;
    }
}
