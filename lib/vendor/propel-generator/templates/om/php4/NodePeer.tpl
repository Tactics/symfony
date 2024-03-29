<?php

// Template for creating base node Peer class on tree table.
//
// $Id: NodePeer.tpl,v 1.10 2005/02/13 12:23:52 micha Exp $

require_once 'propel/engine/builder/om/ClassTools.php';
require_once 'propel/engine/builder/om/PeerBuilder.php';

$npath_colname = '';
$npath_phpname = '';
$npath_len = 0;
$npath_sep = '';

foreach ($table->getColumns() as $col) {
    if ($col->isNodeKey()) {
        $npath_colname = $table->getName() . '.' . strtoupper($col->getName());
        $npath_phpname = $col->getPhpName();
        $npath_len = $col->getSize();
		$npath_sep = $col->getNodeKeySep();
        break;
    }
}

$db = $table->getDatabase();
if($table->getPackage()) {
    $package = $table->getPackage();
} else {
    $package = $targetPackage;
}

$CLASS = $table->getPhpName() . 'NodePeer';
echo '<' . '?' . 'php';

?>


require_once '<?php echo ClassTools::getFilePath($package, $table->getPhpName()) ?>';
require_once '<?php echo ClassTools::getFilePath($package, $table->getPhpName() . 'Node') ?>';


/**
 * Base static class for performing query operations on the tree contained by the
 * '<?php echo $table->getPhpName() ?>' table.
 *
<?php if ($addTimeStamp) { ?>
 * This class was autogenerated by Propel on:
 *
 * [<?php echo $now ?>]
 *
<?php } ?>
 * @package <?php echo $package ?>
 */
class <?php echo $basePrefix . $table->getPhpName() ?>NodePeer
{
    function NPATH_COLNAME() { return '<?php echo $npath_colname ?>'; }
    function NPATH_PHPNAME() { return '<?php echo $npath_phpname ?>'; }
	function NPATH_SEP() { return '<?php echo $npath_sep ?>'; }

    /**
     * Temp function for CodeBase hacks that will go away.
     */
    function isCodeBase(Connection $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'isCodeBase', 1);
		$connection =& Param::get($connection);

		if ($connection === null)
	        $connection =& Propel::getConnection(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        return (get_class($connection) == 'ODBCConnection' &&
                get_class($connection->getAdapter()) == 'CodeBaseAdapter');
    }

    /**
     * Create a new Node at the top of tree. This method will destroy any
     * existing root node (along with its children).
     *
     * Use at your own risk!
     *
     * @param <?php echo $table->getPhpName() ?> Object wrapped by new node.
     * @param Connection Connection to use.
     * @return <?php echo $table->getPhpName() ?>Node
     * @throws PropelException
     */
    function & createNewRootNode(&$obj, Connection $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'createNewRootNode', 2);
	    $connection =& Param::get($connection);

        if ($connection === null)
            $connection =& Propel::getConnection(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        if (Propel::isError($connection)) {
            return $connection;
        }

        $e = $connection->begin();
        if (Creole::isError($e)) { $connection->rollback(); return new PropelException(PROPEL_ERROR_DB, $e); }

        $e = <?php echo $table->getPhpName() ?>NodePeer::deleteNodeSubTree('1', Param::set($connection));
        if (Propel::isError($e)) { $connection->rollback(); return $e; }

        $setNodePath = 'set' . <?php echo $table->getPhpName() ?>NodePeer::NPATH_PHPNAME();
        $obj->$setNodePath('1');

        $e = $obj->save(Param::set($connection));
        if (Propel::isError($e)) { $connection->rollback(); return $e; }

        $e = $connection->commit();
        if (Creole::isError($e)) { $connection->rollback(); return new PropelException(PROPEL_ERROR_DB, $e); }

        return new <?php echo $table->getPhpName() ?>Node(Param::set($obj));
    }

    /**
     * Inserts a new Node at the top of tree. Any existing root node (along with
     * its children) will be made a child of the new root node. This is a
     * safer alternative to createNewRootNode().
     *
     * @param <?php echo $table->getPhpName() ?> Object wrapped by new node.
     * @param Connection Connection to use.
     * @return <?php echo $table->getPhpName() ?>Node
     * @throws PropelException
     */
    function & insertNewRootNode(&$obj, Connection $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'insertNewRootNode', 2);
	    $connection =& Param::get($connection);

		if ($connection === null)
            $connection =& Propel::getConnection(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        $e = $connection->begin();
        if (Creole::isError($e)) { $connection->rollback(); return new PropelException(PROPEL_ERROR_DB, $e); }

        // Move root tree to an invalid node path.
        $e = <?php echo $table->getPhpName() ?>NodePeer::moveNodeSubTree('1', '0', Param::set($connection));
        if (Propel::isError($e)) { $connection->rollback(); return $e; }

        $setNodePath = 'set' . <?php echo $table->getPhpName() ?>NodePeer::NPATH_PHPNAME();

        // Insert the new root node.
        $obj->$setNodePath('1');

        $e = $obj->save(Param::set($connection));
        if (Propel::isError($e)) { $connection->rollback(); return $e; }

        // Move the old root tree as a child of the new root.
        $e = <?php echo $table->getPhpName() ?>NodePeer::moveNodeSubTree('0', '1' . <?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP() . '1', Param::set($connection));
        if (Propel::isError($e)) { $connection->rollback(); return $e; }

        $e = $connection->commit();
        if (Creole::isError($e)) { $connection->rollback(); return new PropelException(PROPEL_ERROR_DB, $e); }

        return new <?php echo $table->getPhpName() ?>Node(Param::set($obj));
    }

    /**
     * Retrieves an array of tree nodes based on specified criteria. Optionally
     * includes all parent and/or child nodes of the matching nodes.
     *
     * @param Criteria Criteria to use.
     * @param boolean True if ancestors should also be retrieved.
     * @param boolean True if descendants should also be retrieved.
     * @param Connection Connection to use.
     * @return array Array of root nodes.
     */
    function & retrieveNodes(&$criteria, $ancestors = false, $descendants = false, Connection $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'retrieveNodes', 4);
	    $connection =& Param::get($connection);
        $criteria =& <?php echo $table->getPhpName() ?>NodePeer::buildFamilyCriteria($criteria, $ancestors, $descendants);
        $rs =& <?php echo $table->getPhpName() ?>Peer::doSelectRS($criteria, Param::set($connection));
        return <?php echo $table->getPhpName() ?>NodePeer::populateNodes($rs, $criteria);
    }

    /**
     * Retrieves a tree node based on a primary key. Optionally includes all
     * parent and/or child nodes of the matching node.
     *
     * @param mixed <?php echo $table->getPhpName() ?> primary key (array for composite keys)
     * @param boolean True if ancestors should also be retrieved.
     * @param boolean True if descendants should also be retrieved.
     * @param Connection Connection to use.
     * @return <?php echo $table->getPhpName() ?>Node
     */
    function & retrieveNodeByPK($pk, $ancestors = false, $descendants = false, $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'retrieveNodeByPK', 4);
	    $connection =& Param::get($connection);
        return new PropelException(PROPEL_ERROR, 'retrieveNodeByPK() not implemented yet.');
    }

    /**
     * Retrieves a tree node based on a node path. Optionally includes all
     * parent and/or child nodes of the matching node.
     *
     * @param string Node path to retrieve.
     * @param boolean True if ancestors should also be retrieved.
     * @param boolean True if descendants should also be retrieved.
     * @param Connection Connection to use.
     * @return <?php echo $table->getPhpName() ?>Node
     */
    function & retrieveNodeByNP($np, $ancestors = false, $descendants = false, $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'retrieveNodeByNP', 4);
	    $connection =& Param::get($connection);
        $criteria =& new Criteria(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());
        $criteria->add(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME(), $np, Criteria::EQUAL());
        $criteria =& <?php echo $table->getPhpName() ?>NodePeer::buildFamilyCriteria($criteria, $ancestors, $descendants);
        $rs =& <?php echo $table->getPhpName() ?>Peer::doSelectRS($criteria, Param::set($connection));
        $nodes =& <?php echo $table->getPhpName() ?>NodePeer::populateNodes($rs, $criteria);
        return (count($nodes) == 1 ? $nodes[0] : null);
    }

    /**
     * Retrieves the root node.
     *
     * @param string Node path to retrieve.
     * @param boolean True if descendants should also be retrieved.
     * @param Connection Connection to use.
     * @return <?php echo $table->getPhpName() ?>Node
     */
    function & retrieveRootNode($descendants = false, $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'retrieveRootNode', 2);
	    $connection =& Param::get($connection);
        return <?php echo $table->getPhpName() ?>NodePeer::retrieveNodeByNP('1', false, $descendants, Param::set($connection));
    }

    /**
     * Moves the node subtree at srcpath to the dstpath. This method is intended
     * for internal use by the BaseNode object. Note that it does not check for
     * preexisting nodes at the dstpath. It also does not update the  node path
     * of any Node objects that might currently be in memory.
     *
     * Use at your own risk!
     *
     * @param string Source node path to move (root of the src subtree).
     * @param string Destination node path to move to (root of the dst subtree).
     * @param Connection Connection to use.
     * @return void
     * @throws PropelException
     * @todo This is currently broken for simulated "onCascadeDelete"s.
     * @todo Need to abstract the SQL better. The CONCAT sql function doesn't
     *       seem to be standardized (i.e. mssql), so maybe it needs to be moved
     *       to DBAdapter.
     */
    function moveNodeSubTree($srcPath, $dstPath, $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'moveNodeSubTree', 3);
	    $connection =& Param::get($connection);

        if (substr($dstPath, 0, strlen($srcPath)) == $srcPath)
            return new PropelException(PROPEL_ERROR, 'Cannot move a node subtree within itself.');

		if ($connection === null)
            $connection =& Propel::getConnection(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        /**
         * Example:
         * UPDATE table
         * SET npath = CONCAT('1.3', SUBSTRING(npath, 6, 74))
         * WHERE npath = '1.2.2' OR npath LIKE '1.2.2.%'
         */

        $npath = <?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME();
		//the following dot isn`t mean`t a nodeKeySeperator
        $setcol = substr($npath, strpos($npath, '.')+1);
        $setcollen = <?php echo $npath_len ?>;
        $db = Propel::getDb(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        // <hack>
        if (<?php echo $table->getPhpName() ?>NodePeer::isCodeBase(Param::set($connection)))
        {
            // This is a hack to get CodeBase working. It will eventually be removed.
            // It is a workaround for the following CodeBase bug:
            //   -Prepared statement parameters cannot be embedded in SQL functions (i.e. CONCAT)
            $sql = "UPDATE " . <?php echo $table->getPhpName() ?>Peer::TABLE_NAME() . " " .
                   "SET $setcol=" . $db->concatString("'$dstPath'", $db->subString($npath, strlen($srcPath)+1, $setcollen)) . " " .
                   "WHERE $npath = '$srcPath' OR $npath LIKE '$srcPath" . <?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP() . "%'";

            $connection->executeUpdate($sql);
        }
        else
        {
        // </hack>
            $sql = "UPDATE " . <?php echo $table->getPhpName() ?>Peer::TABLE_NAME() . " " .
                   "SET $setcol=" . $db->concatString('?', $db->subString($npath, '?', '?')) . " " .
                   "WHERE $npath = ? OR $npath LIKE ?";

            $stmt =& $connection->prepareStatement($sql);
            $stmt->setString(1, $dstPath);
            $stmt->setInt(2, strlen($srcPath)+1);
            $stmt->setInt(3, $setcollen);
            $stmt->setString(4, $srcPath);
            $stmt->setString(5, $srcPath . <?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP() . '%');
            $stmt->executeUpdate();
        // <hack>
        }
        // </hack>
    }

    /**
     * Deletes the node subtree at the specified node path from the database.
     *
     * @param string Node path to delete
     * @param Connection Connection to use.
     * @return void
     * @throws PropelException
     * @todo This is currently broken for simulated "onCascadeDelete"s.
     */
    function deleteNodeSubTree($nodePath, $connection = null)
    {
        Propel::assertParam($connection, '<?php echo $CLASS; ?>', 'deleteNodeSubTree', 2);
	    $connection =& Param::get($connection);

		if ($connection === null)
            $connection =& Propel::getConnection(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());

        /**
         * DELETE FROM table
         * WHERE npath = '1.2.2' OR npath LIKE '1.2.2.%'
         */

        $criteria =& new Criteria(<?php echo $table->getPhpName() ?>Peer::DATABASE_NAME());
        $criteria->add(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME(), $nodePath, Criteria::EQUAL());
        $criteria->addOr(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME(), $nodePath . <?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP() . '%', Criteria::LIKE());
        // For now, we call BasePeer directly since <?php echo $table->getPhpName() ?>Peer tries to
        // do a cascade delete.
        //          <?php echo $table->getPhpName() ?>Peer::doDelete($criteria, Param::set($connection));
        BasePeer::doDelete($criteria, $connection);
    }

    /**
     * Builds the criteria needed to retrieve node ancestors and/or descendants.
     *
     * @param Criteria Criteria to start with
     * @param boolean True if ancestors should be retrieved.
     * @param boolean True if descendants should be retrieved.
     * @return Criteria
     */
    function & buildFamilyCriteria(&$criteria, $ancestors = false, $descendants = false)
    {
        /*
            Example SQL to retrieve nodepath '1.2.3' with both ancestors and descendants:

            SELECT L.NPATH, L.LABEL, test.NPATH, UCASE(L.NPATH)
            FROM test L, test
            WHERE test.NPATH='1.2.3' AND
                 (L.NPATH=SUBSTRING(test.NPATH, 1, LENGTH(L.NPATH)) OR
                  test.NPATH=SUBSTRING(L.NPATH, 1, LENGTH(test.NPATH)))
            ORDER BY UCASE(L.NPATH) ASC
        */

        if ($criteria === null)
            $criteria =& new Criteria(<?php echo $table->getPhpName() ?>::DATABASE_NAME());

        if (!$criteria->getSelectColumns())
            <?php echo $table->getPhpName() ?>Peer::addSelectColumns($criteria);

        $db =& Propel::getDb($criteria->getDbName());

        if (($ancestors || $descendants) && $criteria->size())
        {
            // If we are retrieving ancestors/descendants, we need to do a
            // self-join to locate them. The exception to this is if no search
            // criteria is specified. In this case we're retrieving all nodes
            // anyway, so there is no need to do a self-join.

            // The left-side of the self-join will contain the columns we'll
            // use to build node objects (target node records along with their
            // ancestors and/or descendants). The right-side of the join will
            // contain the target node records specified by the initial criteria.
            // These are used to match the appropriate ancestor/descendant on
            // the left.

            // Specify an alias for the left-side table to use.
            $criteria->addAlias('L', <?php echo $table->getPhpName() ?>Peer::TABLE_NAME());

            // Make sure we have select columns to begin with.
            if (!$criteria->getSelectColumns())
                <?php echo $table->getPhpName() ?>Peer::addSelectColumns($criteria);

            // Replace any existing columns for the right-side table with the
            // left-side alias.
            $selectColumns = $criteria->getSelectColumns();
            $criteria->clearSelectColumns();
            foreach ($selectColumns as $colName)
                $criteria->addSelectColumn(str_replace(<?php echo $table->getPhpName() ?>Peer::TABLE_NAME(), 'L', $colName));

            $a = null;
            $d = null;

            $npathL = <?php echo $table->getPhpName() ?>Peer::alias('L', <?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME());
            $npathR = <?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME();
            $npath_len = <?php echo $npath_len ?>;

            if ($ancestors)
            {
                // For ancestors, match left-side node paths which are contained
                // by right-side node paths.
                $a =& $criteria->getNewCriterion($npathL,
                                                "$npathL=" . $db->subString($npathR, 1, $db->strLength($npathL), $npath_len),
                                                Criteria::CUSTOM());
            }

            if ($descendants)
            {
                // For descendants, match left-side node paths which contain
                // right-side node paths.
                $d =& $criteria->getNewCriterion($npathR,
                                                "$npathR=" . $db->subString($npathL, 1, $db->strLength($npathR), $npath_len),
                                                Criteria::CUSTOM());
            }

            if ($a)
            {
                if ($d) $a->addOr($d);
                $criteria->addAnd($a);
            }
            else if ($d)
            {
                $criteria->addAnd($d);
            }

            // Add the target node path column. This is used by populateNodes().
            $criteria->addSelectColumn($npathR);

            // Sort by node path to speed up tree construction in populateNodes()
            $criteria->addAsColumn('npathlen', $db->strLength($npathL));
            $criteria->addAscendingOrderByColumn('npathlen');
            $criteria->addAscendingOrderByColumn($npathL);
        }
        else
        {
            // Add the target node path column. This is used by populateNodes().
            $criteria->addSelectColumn(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME());

            // Sort by node path to speed up tree construction in populateNodes()
            $criteria->addAsColumn('npathlen', $db->strLength(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME()));
            $criteria->addAscendingOrderByColumn('npathlen');
            $criteria->addAscendingOrderByColumn(<?php echo $table->getPhpName() ?>NodePeer::NPATH_COLNAME());
        }

        return $criteria;
    }

    /**
     * This method reconstructs as much of the tree structure as possible from
     * the given array of objects. Depending on how you execute your query, it
     * is possible for the ResultSet to contain multiple tree fragments (i.e.
     * subtrees). The array returned by this method will contain one entry
     * for each subtree root node it finds. The remaining subtree nodes are
     * accessible from the <?php echo $table->getPhpName() ?>Node methods of the
     * subtree root nodes.
     *
     * @param array Array of <?php echo $table->getPhpName() ?>Node objects
     * @return array Array of <?php echo $table->getPhpName() ?>Node objects
     */
    function & buildTree(&$nodes)
    {
        // Subtree root nodes to return
        $rootNodes = array();
        $copy = $nodes;

        // Build the tree relations
        foreach ($nodes as $key1 => $node)
        {
			//strrpos fix
			$sep = 0;
			while(false !== ($last = strpos($node->getNodePath(), <?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP(), $sep))) {
				$sep = $last + strlen(<?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP());
			}
            $parentPath = ($sep != 0 ? substr($node->getNodePath(), 0, $sep - strlen(<?php echo $table->getPhpName() ?>NodePeer::NPATH_SEP())) : '');
            $parentNode =& Propel::null();

            // Scan other nodes for parent.
            foreach ($copy as $key2 => $pnode)
            {
                if ($pnode->getNodePath() == $parentPath)
                {
                    $parentNode =& $nodes[$key2];
                    break;
                }
            }

            // If parent was found, attach as child, otherwise its a subtree root
            if ($parentNode)
                $parentNode->attachChildNode($nodes[$key1]);
            else
                $rootNodes[] =& $nodes[$key1];
        }

        return $rootNodes;
    }

    /**
     * Populates the <?php echo $table->getPhpName() ?> objects from the
     * specified ResultSet, wraps them in <?php echo $table->getPhpName() ?>Node
     * objects and build the appropriate node relationships.
     * The array returned by this method will only include the initial targets
     * of the query, even if ancestors/descendants were also requested.
     * The ancestors/descendants will be cached in memory and are accessible via
     * the getNode() methods.
     *
     * @param ResultSet
     * @param Criteria
     * @return array Array of <?php $table->getPhpName() ?>Node objects.
     */
    function & populateNodes(&$rs, &$criteria)
    {
        $nodes = array();
        $targets = array();
        $values = array();
		$targetfld = count($criteria->getSelectColumns());

<?php if (!$table->getChildrenColumn()) { ?>
		// set the class once to avoid overhead in the loop
		$cls = <?php echo $table->getPhpName() ?>Peer::getOMClass();
		if (Propel::isError($cls =& Propel::import($cls))) {
		  return $cls;
		}
<?php } ?>

        // populate the object(s)
        while($rs->next())
        {
            if (!isset($nodes[$rs->getString(1)]))
            {
<?php if ($table->getChildrenColumn()) { ?>
				// class must be set each time from the record row
				$cls =& Propel::import(<?php echo $table->getPhpName() ?>Peer::getOMClass($rs, 1));
				if (Propel::isError($cls)) {
					return $cls;
				}
<?php } ?>
				$obj =& new $cls();

				if (Propel::isError($e =& $obj->hydrate($rs))) {
					return $e;
				}

                $nodes[$rs->getString(1)] =& new <?php echo $table->getPhpName() ?>Node(Param::set($obj));
			}
            $node =& $nodes[$rs->getString(1)];

            if ($node->getNodePath() == $rs->getString($targetfld))
                $targets[$node->getNodePath()] =& $node;
        }

        <?php echo $table->getPhpName() ?>NodePeer::buildTree($nodes);

        foreach($targets as $key => $value)
                $values[] =& $targets[$key];

        return $values;
    }

}

?>
