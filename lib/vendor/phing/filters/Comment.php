<?php

/*
 * The class that holds a comment representation.
*/
class Comment {

    /** The prefix for a line comment. */
    private    $_value;

    /*
     * Sets the prefix for this type of line comment.
     *
     * @param string $value The prefix for a line comment of this type.
     *                Must not be <code>null</code>.
     */
    function setValue($value) {
        $this->_value = (string) $value;
    }

    /*
     * Returns the prefix for this type of line comment.
     *
     * @return string The prefix for this type of line comment.
    */
    function getValue() {
        return $this->_value;
    }
}
?>
