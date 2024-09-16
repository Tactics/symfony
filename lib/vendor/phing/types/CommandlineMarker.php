<?php


/**
 * Class to keep track of the position of an Argument.
 */
// <p>This class is there to support the srcfile and targetfile
// elements of &lt;execon&gt; and &lt;transform&gt; - don't know
// whether there might be additional use cases.</p> --SB
class CommandlineMarker {

    private $realPos = -1;
    private $outer;

    public function __construct(Comandline $outer, private $position) {
        $this->outer = $outer;
    }

    /**
     * Return the number of arguments that preceeded this marker.
     *
     * <p>The name of the executable - if set - is counted as the
     * very first argument.</p>
     */
    public function getPosition() {
        if ($this->realPos == -1) {
            $realPos = ($this->outer->executable === null ? 0 : 1);
            for ($i = 0; $i < $position; $i++) {
                $arg = $this->arguments[$i];
                $realPos += count($arg->getParts());
            }
        }
        return $this->realPos;
    }
}

