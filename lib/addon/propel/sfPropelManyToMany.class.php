<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author     Nick Lane <nick.lane@internode.on.net>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfPropelManyToMany.class.php 8288 2008-04-04 13:55:10Z noel $
 */
class sfPropelManyToMany
{
    public static function getColumn($class, $middleClass)
    {
        // find the related class
        $tableMap = call_user_func([$middleClass.'Peer', 'getTableMap']);
        $object_table_name = constant($class.'Peer::TABLE_NAME');
        foreach ($tableMap->getColumns() as $column) {
            if ($column->isForeignKey() && $object_table_name == $column->getRelatedTableName()) {
                return $column;
            }
        }
    }

    public static function getRelatedColumn($class, $middleClass)
    {
        // find the related class
        $tableMap = call_user_func([$middleClass.'Peer', 'getTableMap']);
        $object_table_name = constant($class.'Peer::TABLE_NAME');
        foreach ($tableMap->getColumns() as $column) {
            if ($column->isForeignKey() && $object_table_name != $column->getRelatedTableName()) {
                return $column;
            }
        }
    }

    public static function getRelatedClass($class, $middleClass)
    {
        $column = self::getRelatedColumn($class, $middleClass);

        // we must load all map builder classes
        $classes = sfFinder::type('file')->ignore_version_control()->name('*MapBuilder.php')->in(sfLoader::getModelDirs());
        foreach ($classes as $class) {
            $class_map_builder = basename((string) $class, '.php');
            $map = new $class_map_builder();
            $map->doBuild();
        }

        $tableMap = call_user_func([$middleClass.'Peer', 'getTableMap']);

        return $tableMap->getDatabaseMap()->getTable($column->getRelatedTableName())->getPhpName();
    }

    public static function getAllObjects($object, $middleClass, $criteria = null)
    {
        if (null === $criteria) {
            $criteria = new Criteria();
        }

        $relatedClass = self::getRelatedClass($object::class, $middleClass);

        return call_user_func([$relatedClass.'Peer', 'doSelect'], $criteria);
    }

    /**
     * Gets objects related by a many-to-many relationship, with a middle table.
     *
     * @param $object      the object to get related objects for
     * @param $middleClass the middle class used for the many-to-many relationship
     * @param $criteria    criteria to apply to the selection
     *
     * @return array
     */
    public static function getRelatedObjects($object, $middleClass, $criteria = null)
    {
        if (null === $criteria) {
            $criteria = new Criteria();
        }

        $relatedClass = self::getRelatedClass($object::class, $middleClass);

        $relatedObjects = [];
        $objectMethod = 'get'.$middleClass.'sJoin'.$relatedClass;
        $relatedMethod = 'get'.$relatedClass;
        $rels = $object->$objectMethod($criteria);
        foreach ($rels as $rel) {
            $relatedObjects[] = $rel->$relatedMethod();
        }

        return $relatedObjects;
    }
}
