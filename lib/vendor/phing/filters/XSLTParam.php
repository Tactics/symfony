<?php



/**
 * Class that holds an XSLT parameter.
 */
class XSLTParam {

    private $name;

    private $expr;

    /**
     * Sets param name.
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get param name.
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets expression value.
     * @param string $expr
     */
    public function setExpression($expr) {
        $this->expr = $expr;
    }

    /**
     * Sets expression to dynamic register slot.
     * @param RegisterSlot $expr
     */
    public function setListeningExpression(RegisterSlot $expr) {
        $this->expr = $expr;
    }

    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     * @return string
     */
    public function getExpression() {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        } else {
            return $this->expr;
        }
    }
}

