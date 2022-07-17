<?php


/*
 * Note, this class here should become a nested class to
 * PatternSet (PatternSet:NameEntry) as it is only needed
 * internally.
 * This is not possible with php 4.x right now so we place
 * this class (against good style) in this file.
 */

class PatternSetNameEntry {

    private $name       = null;
    private $ifCond     = null;
    private $unlessCond = null;

    function setName($name) {
        $this->name = (string) $name;
    }


    function setIf($cond) {
        $this->ifCond = (string) $cond;
    }


    function setUnless($cond) {
        $this->unlessCond = (string) $cond;
    }


    function getName() {
        return $this->name;
    }


    function evalName($project) {
        return $this->valid($project) ? $this->name : null;
    }


    function valid($project) {
        if ($this->ifCond !== null && $project->getProperty($this->ifCond) === null) {
            return false;
        } else if ($this->unlessCond !== null && $project->getProperty($this->unlessCond) !== null) {
            return false;
        }
        return true;
    }


    function toString() {
        $buf = $this->name;
        if (($this->ifCond !== null) || ($this->unlessCond !== null)) {
            $buf .= ":";
            $connector = "";

            if ($this->ifCond !== null) {
                $buf .= "if->{$this->ifCond}";
                $connector = ";";
            }
            if ($this->unlessCond !== null) {
                $buf .= "$connector unless->{$this->unlessCond}";
            }
        }
        return $buf;
    }
}
