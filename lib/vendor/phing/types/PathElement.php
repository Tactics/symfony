<?php


/**
 * Helper class, holds the nested <code>&lt;pathelement&gt;</code> values.
 */
class PathElement {

    private $parts = array();
    private $outer;

    public function __construct(Path $outer) {
        $this->outer = $outer;
    }

    public function setDir(PhingFile $loc) {
        $this->parts = array(Path::translateFile($loc->getAbsolutePath()));
    }

    public function setPath($path) {
        $this->parts = Path::translatePath($this->outer->getProject(), $path);
    }

    public function getParts() {
        return $this->parts;
    }
}
