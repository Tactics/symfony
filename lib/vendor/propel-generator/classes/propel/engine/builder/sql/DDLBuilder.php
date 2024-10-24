<?php

/*
 *  $Id: DDLBuilder.php 3752 2007-04-11 09:11:18Z fabien $
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

require_once 'propel/engine/builder/DataModelBuilder.php';

/**
 * Baseclass for SQL DDL-building classes.
 *
 * DDL-building classes are those that build all the SQL DDL for a single table.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.engine.builder.sql
 */
abstract class DDLBuilder extends DataModelBuilder {

	/**
	 * Builds the SQL for current table and returns it as a string.
	 *
	 * This is the main entry point and defines a basic structure that classes should follow.
	 * In most cases this method will not need to be overridden by subclasses.
	 *
	 * @return     string The resulting SQL DDL.
	 */
	public function build()
	{
		$script = "";
		$this->addTable($script);
		$this->addIndices($script);
		$this->addForeignKeys($script);
		return $script;
	}

	/**
	 * Builds the DDL SQL for a Column object.
	 * @return     string
	 */
	public function getColumnDDL(Column $col)
	{
		$platform = $this->getPlatform();
		$domain = $col->getDomain();

		$sb = "";
		$sb .= $this->quoteIdentifier($col->getName()) . " ";
		$sb .= $domain->getSqlType();
		if ($platform->hasSize($domain->getSqlType())) {
			$sb .= $domain->printSize();
		}
		$sb .= " ";
		$sb .= $col->getDefaultSetting() . " ";
		$sb .= $col->getNotNullString() . " ";
		$sb .= $col->getAutoIncrementString();

		return trim($sb);
	}

	/**
	 * Creates a delimiter-delimited string list of column names, quoted using quoteIdentifier().
	 * @param      array Column[] or string[]
	 * @param      string $delim The delimiter to use in separating the column names.
	 * @return     string
	 */
	public function getColumnList($columns, $delim=',')
	{
		$list = [];
		foreach($columns as $col) {
			if ($col instanceof Column) {
				$col = $col->getName();
			}
			$list[] = $this->quoteIdentifier($col);
		}
		return implode($delim, $list);
	}

	/**
	 * This function adds any _database_ start/initialization SQL.
	 * This is designed to be called for a database, not a specific table, hence it is static.
	 * @return     string The DDL is returned as astring.
	 */
	public static function getDatabaseStartDDL()
	{
		return '';
	}

	/**
	 * This function adds any _database_ end/cleanup SQL.
	 * This is designed to be called for a database, not a specific table, hence it is static.
	 * @return     string The DDL is returned as astring.
	 */
	public static function getDatabaseEndDDL()
	{
		return '';
	}

	/**
	 * Adds table definition.
	 * @param      string &$script The script will be modified in this method.
	 */
	abstract protected function addTable(&$script);

	/**
	 * Adds index definitions.
	 * @param      string &$script The script will be modified in this method.
	 */
	abstract protected function addIndices(&$script);

	/**
	 * Adds foreign key constraint definitions.
	 * @param      string &$script The script will be modified in this method.
	 */
	abstract protected function addForeignKeys(&$script);

}
