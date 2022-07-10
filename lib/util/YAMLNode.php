<?php
/**
 * Spyc -- A Simple PHP YAML Class
 * @version 0.2.3 -- 2006-02-04
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
class YAMLNode
{
    public $parent;
    public $id;
    public $data;
    public $indent;
    public $children = false;

    static protected $lastNodeId = 0;

    /**
     * The constructor assigns the node a unique ID.
     *
     * @return void
     */
    public function __construct()
    {
        $this->id = ++self::$lastNodeId;
    }
}
