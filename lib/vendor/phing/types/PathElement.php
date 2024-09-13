<?php


/**
 * Helper class, holds the nested <code>&lt;pathelement&gt;</code> values.
 */
class PathElement {

    private $parts = [];

    public function __construct(private readonly Path $outer)
    {
    }

    public function setDir(PhingFile $loc) {
        $this->parts = [Path::translateFile($loc->getAbsolutePath())];
    }

    public function setPath($path) {
        $this->parts = Path::translatePath($this->outer->getProject(), $path);
    }

    public function getParts() {
        return $this->parts;
    }
}
