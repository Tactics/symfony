<?php
/*
 *  $Id: Criteria.php 561 2007-02-01 02:09:52Z hans $
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

/**
 * This is a utility class for holding criteria information for a query.
 *
 * BasePeer constructs SQL statements based on the values in this class.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Kaspars Jaudzems <kaspars.jaudzems@inbox.lv> (Propel)
 * @author     Frank Y. Kim <frank.kim@clearink.com> (Torque)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Eric Dobbs <eric@dobbse.net> (Torque)
 * @author     Henning P. Schmiedehausen <hps@intermeta.de> (Torque)
 * @author     Sam Joseph <sam@neurogrid.com> (Torque)
 * @version    $Revision: 561 $
 * @package    propel.util
 */
class Criteria implements IteratorAggregate {

	/** Comparison type. */
	const EQUAL = "=";

	/** Comparison type. */
	const NOT_EQUAL = "<>";

	/** Comparison type. */
	const ALT_NOT_EQUAL = "!=";

	/** Comparison type. */
	const GREATER_THAN = ">";

	/** Comparison type. */
	const LESS_THAN = "<";

	/** Comparison type. */
	const GREATER_EQUAL = ">=";

	/** Comparison type. */
	const LESS_EQUAL = "<=";

	/** Comparison type. */
	const LIKE = " LIKE ";

	/** Comparison type. */
	const NOT_LIKE = " NOT LIKE ";

	/** PostgreSQL comparison type */
	const ILIKE = " ILIKE ";

	/** PostgreSQL comparison type */
	const NOT_ILIKE = " NOT ILIKE ";

	/** Comparison type. */
	const CUSTOM = "CUSTOM";

	/** Comparison type. */
	const DISTINCT = "DISTINCT ";

	/** Comparison type. */
	const IN = " IN ";

	/** Comparison type. */
	const NOT_IN = " NOT IN ";

	/** Comparison type. */
	const ALL = "ALL ";

	/** Comparison type. */
	const JOIN = "JOIN";

	/** Binary math operator: AND */
	const BINARY_AND = "&";

	/** Binary math operator: OR */
	const BINARY_OR = "|";

	/** "Order by" qualifier - ascending */
	const ASC = "ASC";

	/** "Order by" qualifier - descending */
	const DESC = "DESC";

	/** "IS NULL" null comparison */
	const ISNULL = " IS NULL ";

	/** "IS NOT NULL" null comparison */
	const ISNOTNULL = " IS NOT NULL ";

	/** "CURRENT_DATE" ANSI SQL function */
	const CURRENT_DATE = "CURRENT_DATE";

	/** "CURRENT_TIME" ANSI SQL function */
	const CURRENT_TIME = "CURRENT_TIME";

	/** "CURRENT_TIMESTAMP" ANSI SQL function */
	const CURRENT_TIMESTAMP = "CURRENT_TIMESTAMP";

	/** "LEFT JOIN" SQL statement */
	const LEFT_JOIN = "LEFT JOIN";

	/** "RIGHT JOIN" SQL statement */
	const RIGHT_JOIN = "RIGHT JOIN";

	/** "INNER JOIN" SQL statement */
	const INNER_JOIN = "INNER JOIN";

	private $ignoreCase = false;
	private $singleRecord = false;
	private $selectModifiers = [];
	private $selectColumns = [];
	private $orderByColumns = [];
	private $groupByColumns = [];
	private $having = null;
	private $asColumns = [];
	private $joins = [];

	/** The name of the database. */
	private $dbName;

	/** The name of the database as given in the contructor. */
	private $originalDbName;

	/**
	 * To limit the number of rows to return.  <code>0</code> means return all
	 * rows.
	 */
	private $limit = 0;

	/** To start the results at a row other than the first one. */
	private $offset = 0;

	// flag to note that the criteria involves a blob.
	private $blobFlag = null;

	private $aliases = [];

	private $useTransaction = false;

	/**
	 * Primary storage of criteria data.
	 * @var        array
	 */
	private $map = [];

	/**
	 * Creates a new instance with the default capacity which corresponds to
	 * the specified database.
	 *
	 * @param   string $dbName The dabase name.
	 */
	public function __construct($dbName = null)
	{
		$this->setDbName($dbName);
		$this->originalDbName = $dbName;
	}

	/**
	 * Implementing SPL IteratorAggregate interface.  This allows
	 * you to foreach() over a Criteria object.
	 */
	public function getIterator() : \Traversable
	{
		return new \CriterionIterator($this);
	}

		/**
		 * Get the criteria map.
		 * @return     array
		 */
		public function getMap()
		{
			return $this->map;
		}

	/**
	 * Brings this criteria back to its initial state, so that it
	 * can be reused as if it was new. Except if the criteria has grown in
	 * capacity, it is left at the current capacity.
	 * @return     void
	 */
	public function clear()
	{
		$this->map = [];
		$this->ignoreCase = false;
		$this->singleRecord = false;
		$this->selectModifiers = [];
		$this->selectColumns = [];
		$this->orderByColumns = [];
		$this->groupByColumns = [];
		$this->having = null;
		$this->asColumns = [];
		$this->joins = [];
		$this->dbName = $this->originalDbName;
		$this->offset = 0;
		$this->limit = -1;
		$this->blobFlag = null;
		$this->aliases = [];
		$this->useTransaction = false;
	}

	/**
	 * Add an AS clause to the select columns. Usage:
	 *
	 * <code>
	 * Criteria myCrit = new Criteria();
	 * myCrit->addAsColumn("alias", "ALIAS(".MyPeer::ID.")");
	 * </code>
	 *
	 * @param      string $name Wanted Name of the column (alias).
	 * @param      string $clause SQL clause to select from the table
	 *
	 * If the name already exists, it is replaced by the new clause.
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAsColumn($name, $clause)
	{
		$this->asColumns[$name] = $clause;
		return $this;
	}

	/**
	 * Get the column aliases.
	 *
	 * @return     array An assoc array which map the column alias names
	 * to the alias clauses.
	 */
	public function getAsColumns()
	{
		return $this->asColumns;
	}

    /**
	 * Returns the column name associated with an alias (AS-column).
	 *
	 * @param      string $as alias
	 * @return     string $string
	 */
	public function getColumnForAs($as)
	{
		if (!isset($this->asColumns[$as])) {
			return null;
		}
        return $this->asColumns[$as];
	}

	/**
	 * Allows one to specify an alias for a table that can
	 * be used in various parts of the SQL.
	 *
	 * @param      string $alias
	 * @param      string $table
	 * @return     void
	 */
	public function addAlias($alias, $table)
	{
		$this->aliases[$alias] = $table;
	}

	/**
	 * Returns the table name associated with an alias.
	 *
	 * @param      string $alias
	 * @return     string $string
	 */
	public function getTableForAlias($alias)
	{
		if (!isset($this->aliases[$alias])) {
			return null;
		}
        return $this->aliases[$alias];
	}

	/**
	 * Get the keys for the criteria map.
	 * @return     array
	 */
	public function keys()
	{
		return array_keys($this->map);
	}

	/**
	 * Does this Criteria object contain the specified key?
	 *
	 * @param      string $column [table.]column
	 * @return     boolean True if this Criteria object contain the specified key.
	 */
	public function containsKey($column)
	{
		// must use array_key_exists() because the key could
		// exist but have a NULL value (that'd be valid).
		return array_key_exists($column, $this->map);
	}

	/**
  * Will force the sql represented by this criteria to be executed within
  * a transaction.  This is here primarily to support the oid type in
  * postgresql.  Though it can be used to require any single sql statement
  * to use a transaction.
  *
  * @return     void
  */
 public function setUseTransaction(mixed $v)
	{
		$this->useTransaction = (boolean) $v;
	}

	/**
	 * called by BasePeer to determine whether the sql command specified by
	 * this criteria must be wrapped in a transaction.
	 *
	 * @return     bool
	 */
	public function isUseTransaction()
	{
		return $this->useTransaction;
	}

	/**
	 * Method to return criteria related to columns in a table.
	 *
     * @param      string $column Column name.
	 * @return     Criterion A Criterion or null if $column is invalid.
	 */
	public function getCriterion($column)
	{
		if (!isset($this->map[$column])) {
			return null;
		}
        return $this->map[$column];
	}

	/**
  * Method to return criterion that is not added automatically
  * to this Criteria.  This can be used to chain the
  * Criterions to form a more complex where clause.
  *
  * @param      string $column Full name of column (for example TABLE.COLUMN).
  * @param      string $comparison
  * @return     Criterion A Criterion
  */
 public function getNewCriterion($column, mixed $value, $comparison = null)
	{
		return new \Criterion($this, $column, $value, $comparison);
	}

	/**
	 * Method to return a String table name.
	 *
	 * @param      string $name A String with the name of the key.
	 * @return     string A String with the value of the object at key.
	 */
	public function getColumnName($name)
	{
		if ( isset ( $this->map[$name] ) ) {
			return $this->map[$name]->getColumn();
		}
		return null;
	}

	/**
	 * Shortcut method to get an array of columns indexed by table.
	 * @return     array array(table => array(table.column1, table.column2))
	 */
	public function getTablesColumns()
	{
		$tables = [];
		foreach ( array_keys ( $this->map ) as $key) {
			$t = substr ( $key, 0, strpos ( $key, '.' ) );
			if ( ! isset ( $tables[$t] ) ) {
				$tables[$t] = [$key];
			} else {
				$tables[$t][] = $key;
			}
		}
		return $tables;
	}

	/**
	 * Method to return a comparison String.
	 *
	 * @param      string $key String name of the key.
	 * @return     string A String with the value of the object at key.
	 */
	public function getComparison($key)
	{
		if ( isset ( $this->map[$key] ) ) {
			return $this->map[$key]->getComparison();
		}
		return null;
	}

	/**
	 * Get the Database(Map) name.
	 *
	 * @return     string A String with the Database(Map) name.
	 */
	public function getDbName()
	{
		return $this->dbName;
	}

	/**
	 * Set the DatabaseMap name.  If <code>null</code> is supplied, uses value
	 * provided by <code>Propel::getDefaultDB()</code>.
	 *
	 * @param      string $dbName A String with the Database(Map) name.
	 * @return     void
	 */
	public function setDbName($dbName = null)
	{
		$this->dbName = ($dbName ?? Propel::getDefaultDB());
	}

	/**
	 * Method to return a String table name.
	 *
	 * @param      string $name A String with the name of the key.
	 * @return     string A String with the value of table for criterion at key.
	 */
	public function getTableName($name)
	{
		if ( isset ( $this->map[$name] ) ) {
			return $this->map[$name]->getTable();
		}
		return null;
	}

	/**
	 * Method to return the value that was added to Criteria.
	 *
	 * @param      string $name A String with the name of the key.
	 * @return     mixed The value of object at key.
	 */
	public function getValue($name)
	{
		if ( isset ( $this->map[$name] ) ) {
			return $this->map[$name]->getValue();
		}
		return null;
	}

	/**
	 * An alias to getValue() -- exposing a Hashtable-like interface.
	 *
	 * @param      string $key An Object.
	 * @return     mixed The value within the Criterion (not the Criterion object).
	 */
	public function get($key)
	{
		return $this->getValue($key);
	}

    /**
  * Overrides Hashtable put, so that this object is returned
  * instead of the value previously in the Criteria object.
  * The reason is so that it more closely matches the behavior
  * of the add() methods. If you want to get the previous value
  * then you should first Criteria.get() it yourself. Note, if
  * you attempt to pass in an Object that is not a String, it will
  * throw a NPE. The reason for this is that none of the add()
  * methods support adding anything other than a String as a key.
  *
  * @param      string $key
  * @return     Criteria Instance of self
  */
 public function put($key, mixed $value)
	{
		return $this->add($key, $value);
	}

	/**
	 * Copies all of the mappings from the specified Map to this Criteria
	 * These mappings will replace any mappings that this Criteria had for any
	 * of the keys currently in the specified Map.
	 *
	 * if the map was another Criteria, its attributes are copied to this
	 * Criteria, overwriting previous settings.
	 *
	 * @param      mixed $t Mappings to be stored in this map.
	 */
	public function putAll(mixed $t)
	{
		if (is_array($t)) {

			foreach ($t as $key=>$value) {

				if ($value instanceof Criterion) {

					$this->map[$key] = $value;

				} else {

					$this->put($key, $value);

				}

			}

		} elseif ($t instanceof Criteria) {

			$this->joins = $t->joins;

		}

	}


	/**
  * This method adds a new criterion to the list of criterias.
  * If a criterion for the requested column already exists, it is
  * replaced. If is used as follow:
  *
  * <p>
  * <code>
  * $crit = new Criteria();
  * $crit->add(&quot;column&quot;,
  *                                      &quot;value&quot;
  *                                      &quot;Criteria::GREATER_THAN&quot;);
  * </code>
  *
  * Any comparison can be used.
  *
  * The name of the table must be used implicitly in the column name,
  * so the Column name must be something like 'TABLE.id'. If you
  * don't like this, you can use the add(table, column, value) method.
  *
  * @param      string|Criterion $p1 critOrColumn The column to run the comparison on, or Criterion object.
  * @param      string $comparison A String.
  * @return     Criteria A modified Criteria object.
  */
 public function add($p1, mixed $value = null, $comparison = null)
	{
		if ($p1 instanceof Criterion) {
			$c = $p1;
			$this->map[$c->getTable() . '.' . $c->getColumn()] = $c;
		} else {
			$column = $p1;
			$this->map[$column] = new \Criterion($this, $column, $value, $comparison);
		}
		return $this;
	}

	/**
	 * This is the way that you should add a straight (inner) join of two tables.  For
	 * example:
	 *
	 * <p>
	 * AND PROJECT.PROJECT_ID=FOO.PROJECT_ID
	 * <p>
	 *
	 * left = PROJECT.PROJECT_ID
	 * right = FOO.PROJECT_ID
	 *
	 * @param      string $left A String with the left side of the join.
	 * @param      string $right A String with the right side of the join.
		 * @param      string $operator A String with the join operator e.g. LEFT JOIN, ...
	 * @return     Criteria A modified Criteria object.
	 */
	public function addJoin($left, $right, $operator = null)
	{
		$this->joins [] = new Join($left, $right, $operator);

		return $this;
	}

	/**
	 * Get the array of Joins.  This method is meant to
	 * be called by BasePeer.
	 * @return     Join[] an array which contains objects of type Join,
	 *         or an empty array if the criteria does not contains any joins
	 */
	function & getJoins()
	{
		return $this->joins;
	}

	/**
	 * get one side of the set of possible joins.  This method is meant to
	 * be called by BasePeer.
     *
     * @throws PropelException
	 * @deprecated This method is no longer used by BasePeer.
	 */
	public function getJoinL(): never
	{
		throw new PropelException("getJoinL() in Criteria is no longer supported!");
	}

	/**
	 * get one side of the set of possible joins.  This method is meant to
	 * be called by BasePeer.
     *
     * @throws PropelException
     * @deprecated This method is no longer used by BasePeer.
	 */
	public function getJoinR(): never
	{
		throw new PropelException("getJoinR() in Criteria is no longer supported!");
	}

	/**
	 * Adds "ALL " to the SQL statement.
	 * @return     void
	 */
	public function setAll()
	{
		$this->selectModifiers[] = self::ALL;
	}

	/**
	 * Adds "DISTINCT " to the SQL statement.
	 * @return     void
	 */
	public function setDistinct()
	{
		$this->selectModifiers[] = self::DISTINCT;
	}

	/**
	 * Sets ignore case.
	 *
	 * @param      boolean $b True if case should be ignored.
	 * @return     Criteria A modified Criteria object.
	 */
	public function setIgnoreCase($b)
	{
		$this->ignoreCase = (boolean) $b;
		return $this;
	}

	/**
	 * Is ignore case on or off?
	 *
	 * @return     boolean True if case is ignored.
	 */
	public function isIgnoreCase()
	{
		return $this->ignoreCase;
	}

	/**
	 * Set single record?  Set this to <code>true</code> if you expect the query
	 * to result in only a single result record (the default behaviour is to
	 * throw a PropelException if multiple records are returned when the query
	 * is executed).  This should be used in situations where returning multiple
	 * rows would indicate an error of some sort.  If your query might return
	 * multiple records but you are only interested in the first one then you
	 * should be using setLimit(1).
	 *
	 * @param      mixed $b set to <code>true</code> if you expect the query to select just
	 * one record.
	 * @return     Criteria A modified Criteria object.
	 */
	public function setSingleRecord(mixed $b)
	{
		$this->singleRecord = (boolean) $b;
		return $this;
	}

	/**
	 * Is single record?
	 *
	 * @return     boolean True if a single record is being returned.
	 */
	public function isSingleRecord()
	{
		return $this->singleRecord;
	}

	/**
	 * Set limit.
	 *
	 * @param      int $limit An int with the value for limit.
	 * @return     Criteria A modified Criteria object.
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Get limit.
	 *
	 * @return     int An int with the value for limit.
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Set offset.
	 *
	 * @param      int $offset An int with the value for offset.
	 * @return     Criteria A modified Criteria object.
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Get offset.
	 *
	 * @return     int An int with the value for offset.
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Add select column.
	 *
	 * @param      string $name A String with the name of the select column.
	 * @return     Criteria A modified Criteria object.
	 */
	public function addSelectColumn($name)
	{
		$this->selectColumns[] = $name;
		return $this;
	}

	/**
	 * Get select columns.
	 *
	 * @return     array An array with the name of the select
	 * columns.
	 */
	public function getSelectColumns()
	{
		return $this->selectColumns;
	}

	/**
	 * Clears current select columns.
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function clearSelectColumns() {
		$this->selectColumns = [];
		$this->asColumns = [];
		return $this;
	}

	/**
	 * Get select modifiers.
	 *
	 * @return     array An array with the select modifiers.
	 */
	public function getSelectModifiers()
	{
		return $this->selectModifiers;
	}

	/**
	 * Add group by column name.
	 *
	 * @param      string $groupBy The name of the column to group by.
	 * @return     Criteria A modified Criteria object.
	 */
	public function addGroupByColumn($groupBy)
	{
		$this->groupByColumns[] = $groupBy;
		return $this;
	}

	/**
	 * Add order by column name, explicitly specifying ascending.
	 *
	 * @param      string $name The name of the column to order by.
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAscendingOrderByColumn($name)
	{
		$this->orderByColumns[] = $name . ' ' . self::ASC;
		return $this;
	}

	/**
	 * Add order by column name, explicitly specifying descending.
	 *
	 * @param      string $name The name of the column to order by.
	 * @return     Criteria The modified Criteria object.
	 */
	public function addDescendingOrderByColumn($name)
	{
		$this->orderByColumns[] = $name . ' ' . self::DESC;
		return $this;
	}

	/**
	 * Get order by columns.
	 *
	 * @return     array An array with the name of the order columns.
	 */
	public function getOrderByColumns()
	{
		return $this->orderByColumns;
	}

	/**
	 * Clear the order-by columns.
	 *
	 * @return     Criteria
	 */
	public function clearOrderByColumns()
	{
		$this->orderByColumns = [];
		return $this;
	}

	/**
	 * Clear the group-by columns.
	 *
	 * @return     Criteria
	 */
	public function clearGroupByColumns()
	{
		$this->groupByColumns = [];
		return $this;
	}

	/**
	 * Get group by columns.
	 *
	 * @return     array
	 */
	public function getGroupByColumns()
	{
		return $this->groupByColumns;
	}

	/**
	 * Get Having Criterion.
	 *
	 * @return     Criterion A Criterion object that is the having clause.
	 */
	public function getHaving()
	{
		return $this->having;
	}

	/**
	 * Remove an object from the criteria.
	 *
	 * @param      string $key A string with the key to be removed.
	 * @return     mixed The removed value.
	 */
	public function remove($key)
	{
		$c = $this->map[$key] ?? null;
		unset($this->map[$key]);
		if ($c instanceof Criterion) {
			return $c->getValue();
		}
		return $c;
	}

	/**
	 * Build a string representation of the Criteria.
	 *
	 * @return     string A String with the representation of the Criteria.
	 */
	public function toString()
	{
		$sb = "Criteria:: ";

		try {

            $params = [];
			$sb .= "\nCurrent Query SQL (may not be complete or applicable): "
			  . BasePeer::createSelectSql($this, $params);

			$sb .= "\nParameters to replace: " . var_export($params, true);

		} catch (Exception $exc) {
			$sb .= "(Error: " . $exc->getMessage() . ")";
		}

		return $sb;
	}

	/**
	 * Returns the size (count) of this criteria.
	 * @return     int
	 */
	public function size()
	{
		return count($this->map);
	}

	/**
  * This method checks another Criteria to see if they contain
  * the same attributes and hashtable entries.
  *
  * @return     boolean
  */
 public function equals(mixed $crit)
	{
		$isEquiv = false;
		if ($crit === null || !($crit instanceof Criteria)) {
			$isEquiv = false;
		} elseif ($this === $crit) {
			$isEquiv = true;
		} elseif ($this->size() === $crit->size()) {

			// Important: nested criterion objects are checked

			$criteria = $crit; // alias
			if ($this->offset === $criteria->getOffset()
				&& $this->limit === $criteria->getLimit()
				&& $this->ignoreCase === $criteria->isIgnoreCase()
				&& $this->singleRecord === $criteria->isSingleRecord()
				&& $this->dbName === $criteria->getDbName()
				&& $this->selectModifiers === $criteria->getSelectModifiers()
				&& $this->selectColumns === $criteria->getSelectColumns()
				&& $this->orderByColumns === $criteria->getOrderByColumns()
				&& $this->groupByColumns === $criteria->getGroupByColumns()
			   )
			{
				$isEquiv = true;
				foreach($criteria->keys() as $key) {
					if ($this->containsKey($key)) {
						$a = $this->getCriterion($key);
						$b = $criteria->getCriterion($key);
						if (!$a->equals($b)) {
							$isEquiv = false;
							break;
						}
					} else {
						$isEquiv = false;
						break;
					}
				}
			}
		}
		return $isEquiv;
	}

	/**
	 * This method adds a prepared Criterion object to the Criteria as a having clause.
	 * You can get a new, empty Criterion object with the
	 * getNewCriterion() method.
	 *
	 * <p>
	 * <code>
	 * $crit = new Criteria();
	 * $c = $crit->getNewCriterion(BasePeer::ID, 5, Criteria::LESS_THAN);
	 * $crit->addHaving($c);
	 * </code>
	 *
	 * @param      Criterion $having A Criterion object
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addHaving(Criterion $having)
	{
		$this->having = $having;
		return $this;
	}

	/**
	 * This method adds a new criterion to the list of criterias.
	 * If a criterion for the requested column already exists, it is
	 * "AND"ed to the existing criterion.
	  *
	 * addAnd(column, value, comparison)
	 * <code>
	 * $crit = $orig_crit->addAnd(&quot;column&quot;,
	 *                                      &quot;value&quot;
	 *                                      &quot;Criterion::GREATER_THAN&quot;);
	 * </code>
	 *
	 * addAnd(column, value)
	 * <code>
	 * $crit = $orig_crit->addAnd(&quot;column&quot;, &quot;value&quot;);
	 * </code>
	 *
	 * addAnd(Criterion)
	 * <code>
	 * $crit = new Criteria();
	 * $c = $crit->getNewCriterion(BasePeer::ID, 5, Criteria::LESS_THAN);
	 * $crit->addAnd($c);
	 * </code>
	 *
	 * Any comparison can be used, of course.
	 *
     * @param string|Criterion  $p1 string column or Criterion
     * @param mixed             $p2 value
     * @param string            $p3 comparison
	 *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addAnd($p1, mixed $p2 = null, $p3 = null)
	{
		if ($p3 !== null) {
			// addAnd(column, value, comparison)
			$oc = $this->getCriterion($p1);
			$nc = new \Criterion($this, $p1, $p2, $p3);
			if ($oc === null) {
				$this->map[$p1] = $nc;
			} else {
				$oc->addAnd($nc);
			}
		} elseif ($p2 !== null) {
			// addAnd(column, value)
			$this->addAnd($p1, $p2, self::EQUAL);
		} elseif ($p1 instanceof Criterion) {
			// addAnd(Criterion)
			$c = $p1;
			$oc = $this->getCriterion($c->getTable() . '.' . $c->getColumn());
			if ($oc === null) {
				$this->add($c);
			} else {
				$oc->addAnd($c);
			}
		} elseif ($p2 === null && $p3 === null) {
			// client has not specified $p3 (comparison)
			// which means Criteria::EQUAL but has also specified $p2 == null
			// which is a valid combination we should handle by creating "IS NULL"
			$this->addAnd($p1, $p2, self::EQUAL);
		}
		return $this;
	}

	/**
	 * This method adds a new criterion to the list of criterias.
	 * If a criterion for the requested column already exists, it is
	 * "OR"ed to the existing criterion.
	 *
	 * Any comparison can be used.
	 *
	 * Supports a number of different signatures:
	 *
	 * addOr(column, value, comparison)
	 * <code>
	 * $crit = $orig_crit->addOr(&quot;column&quot;,
	 *                                      &quot;value&quot;
	 *                                      &quot;Criterion::GREATER_THAN&quot;);
	 * </code>
	 *
	 * addOr(column, value)
	 * <code>
	 * $crit = $orig_crit->addOr(&quot;column&quot;, &quot;value&quot;);
	 * </code>
	 *
	 * addOr(Criterion)
	 *
     * @param string|Criterion  $p1 string column or Criterion
     * @param mixed             $p2 value
     * @param string            $p3 comparison
     *
	 * @return     Criteria A modified Criteria object.
	 */
	public function addOr($p1, mixed $p2 = null, $p3 = null)
	{
		if ($p3 !== null) {
			// addOr(column, value, comparison)
			$oc = $this->getCriterion($p1);
			$nc = new \Criterion($this, $p1, $p2, $p3);
			if ($oc === null) {
				$this->map[$p1] = $nc;
			} else {
				$oc->addOr($nc);
			}
		} elseif ($p2 !== null) {
			// addOr(column, value)
			$this->addOr($p1, $p2, self::EQUAL);
		} elseif ($p1 instanceof Criterion) {
			// addOr(Criterion)
			$c = $p1;
			$oc = $this->getCriterion($c->getTable() . '.' . $c->getColumn());
			if ($oc === null) {
				$this->add($c);
			} else {
				$oc->addOr($c);
			}
		} elseif ($p2 === null && $p3 === null) {
			// client has not specified $p3 (comparison)
			// which means Criteria::EQUAL but has also specified $p2 == null
			// which is a valid combination we should handle by creating "IS NULL"
			$this->addOr($p1, $p2, self::EQUAL);
		}

		return $this;
	}
}
