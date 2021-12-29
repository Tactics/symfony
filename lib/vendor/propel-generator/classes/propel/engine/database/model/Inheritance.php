<?php

/*
 *  $Id: Inheritance.php 536 2007-01-10 14:30:38Z heltem $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

require_once 'propel/engine/database/model/XMLElement.php';

/**
 * A Class for information regarding possible objects representing a table
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     John McNally <jmcnally@collab.net> (Torque)
 * @version    $Revision: 536 $
 * @package    propel.engine.database.model
 */
class Inheritance extends XMLElement {

	private $key;
	private $className;
	private $pkg;
	private $ancestor;
	private $parent;

	/**
	 * Sets up the Inheritance object based on the attributes that were passed to loadFromXML().
	 * @see        parent::loadFromXML()
	 */
	protected function setupObject()
	{
		$this->key = $this->getAttribute("key");
		$this->className = $this->getAttribute("class");
		$this->pkg = $this->getAttribute("package");
		$this->ancestor = $this->getAttribute("extends");
	}

	/**
	 * Get the value of key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Set the value of key.
	 */
	public function setKey($v): void
    {
		$this->key = $v;
	}

	/**
	 * Get the value of parent.
	 */
	public function getColumn()
	{
		return $this->parent;
	}

	/**
	 * Set the value of parent.
	 */
	public function setColumn(Column  $v): void
    {
		$this->parent = $v;
	}

	/**
	 * Get the value of className.
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * Set the value of className.
	 */
	public function setClassName($v): void
    {
		$this->className = $v;
	}

	/**
	 * Get the value of package.
	 */
	public function getPackage()
	{
		return $this->pkg;
	}

	/**
	 * Set the value of package.
	 */
	public function setPackage($v): void
    {
		$this->pkg = $v;
	}

	/**
	 * Get the value of ancestor.
	 */
	public function getAncestor()
	{
		return $this->ancestor;
	}

	/**
	 * Set the value of ancestor.
	 */
	public function setAncestor($v): void
    {
		$this->ancestor = $v;
	}

	/**
	 * String representation of the foreign key. This is an xml representation.
	 */
	public function toString(): string
    {
		$result = " <inheritance key=\""
			  . $this->key
			  . "\" class=\""
			  . $this->className
			  . '"';

		if ($this->ancestor !== null) {
			$result .= " extends=\""
				  . $this->ancestor
				  . '"';
		}

		$result .= "/>";

		return $result;
	}
}
