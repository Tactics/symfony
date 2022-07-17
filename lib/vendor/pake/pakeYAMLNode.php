<?php

/**
 * Spyc -- A Simple PHP YAML Class
 * @version 0.2.2 -- 2006-01-29
 * @author Chris Wanstrath <chris@ozmm.org>
 * @link http://spyc.sourceforge.net/
 * @copyright Copyright 2005-2006 Chris Wanstrath
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package Spyc
 */

/**
 * A node, used by Spyc for parsing YAML.
 * @package Spyc
 */
class pakeYAMLNode {
    /**#@+
     * @access public
     * @var string
     */
    public $parent;
    public $id;
    /**#@+*/
    /**
     * @access public
     * @var mixed
     */
    public $data;
    /**
     * @access public
     * @var int
     */
    public $indent;
    /**
     * @access public
     * @var bool
     */
    public $children = false;

    /**
     * The constructor assigns the node a unique ID.
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = uniqid('');
    }
}
