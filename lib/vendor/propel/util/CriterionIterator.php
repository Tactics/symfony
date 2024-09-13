<?php


// --------------------------------------------------------------------
// Criterion Iterator class -- allows foreach($criteria as $criterion)
// --------------------------------------------------------------------

/**
 * Class that implements SPL Iterator interface.  This allows foreach() to
 * be used w/ Criteria objects.  Probably there is no performance advantage
 * to doing it this way, but it makes sense -- and simpler code.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.util
 */
class CriterionIterator implements Iterator {

    private $idx = 0;
    private $criteriaKeys;
    private $criteriaSize;

    public function __construct(private readonly Criteria $criteria) {
        $this->criteriaKeys = $this->criteria->keys();
        $this->criteriaSize = count($this->criteriaKeys);
    }

    public function rewind() : void {
        $this->idx = 0;
    }

    public function valid() : bool {
        return $this->idx < $this->criteriaSize;
    }

    public function key() : mixed {
        return $this->criteriaKeys[$this->idx];
    }

    public function current() : mixed {
        return $this->criteria->getCriterion($this->criteriaKeys[$this->idx]);
    }

    public function next() : void {
        $this->idx++;
    }

}
