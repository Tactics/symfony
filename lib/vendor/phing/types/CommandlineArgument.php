<?php


/**
 * "Inner" class used for nested xml command line definitions.
 */
class CommandlineArgument {

    private $parts = array();
    private $outer;

    public function __construct(Commandline $outer) {
        $this->outer = $outer;
    }

    /**
     * Sets a single commandline argument.
     *
     * @param string $value a single commandline argument.
     */
    public function setValue($value) {
        $this->parts = array($value);
    }

    /**
     * Line to split into several commandline arguments.
     *
     * @param line line to split into several commandline arguments
     */
    public function setLine($line) {
        if ($line === null) {
            return;
        }
        $this->parts = $this->outer->translateCommandline($line);
    }

    /**
     * Sets a single commandline argument and treats it like a
     * PATH - ensures the right separator for the local platform
     * is used.
     *
     * @param value a single commandline argument.
     */
    public function setPath($value) {
        $this->parts = array( (string) $value );
    }

    /**
     * Sets a single commandline argument to the absolute filename
     * of the given file.
     *
     * @param value a single commandline argument.
     */
    public function setFile(PhingFile $value) {
        $this->parts = array($value->getAbsolutePath());
    }

    /**
     * Returns the parts this Argument consists of.
     * @return array string[]
     */
    public function getParts() {
        return $this->parts;
    }
}
