<?php

/**
 * sfMessageSource_SQLite class file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the BSD License.
 *
 * Copyright(c) 2004 by Qiang Xue. All rights reserved.
 *
 * To contact the author write to {@link mailto:qiang.xue@gmail.com Qiang Xue}
 * The latest version of PRADO can be obtained from:
 * {@link http://prado.sourceforge.net/}
 *
 * @author     Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version    $Id: sfMessageSource_SQLite.class.php 6806 2007-12-29 07:53:10Z fabien $
 */

/**
 * Get the I18N utility file, contains the DSN parser.
 */

/**
 * sfMessageSource_SQLite class.
 *
 * Retrieve the message translation from a SQLite database.
 *
 * See the MessageSource::factory() method to instantiate this class.
 *
 * SQLite schema:
 *
 * CREATE TABLE catalogue (
 *   cat_id INTEGER PRIMARY KEY,
 *   name VARCHAR NOT NULL,
 *   source_lang VARCHAR ,
 *   target_lang VARCHAR ,
 *   date_created INT,
 *   date_modified INT,
 *   author VARCHAR);
 *
 * CREATE TABLE trans_unit (
 *   msg_id INTEGER PRIMARY KEY,
 *   cat_id INTEGER NOT NULL DEFAULT '1',
 *   id VARCHAR,
 *   source TEXT,
 *   target TEXT,
 *   comments TEXT,
 *   date_added INT,
 *   date_modified INT,
 *   author VARCHAR,
 *   translated INT(1) NOT NULL DEFAULT '0');
 *
 * Propel schema (in .xml format):
 *
 *  <database ...>
 *    ...
 *    <table name="catalogue">
 *     <column name="cat_id" type="integer" required="true" primaryKey="true" autoincrement="true" />
 *     <column name="name" type="varchar" size="100" />
 *     <column name="source_lang" type="varchar" size="100" />
 *     <column name="target_lang" type="varchar" size="100" />
 *     <column name="date_created" type="timestamp" />
 *     <column name="date_modified" type="timestamp" />
 *     <column name="author" type="varchar" size="255" />
 *    </table>
 *
 *    <table name="trans_unit">
 *     <column name="msg_id" type="integer" required="true" primaryKey="true" autoincrement="true" />
 *     <column name="cat_id" type="integer" />
 *       <foreign-key foreignTable="catalogue" onDelete="cascade">
 *         <reference local="cat_id" foreign="cat_id"/>
 *       </foreign-key>
 *     <column name="id" type="varchar" size="255" />
 *     <column name="source" type="longvarchar" />
 *     <column name="target" type="longvarchar" />
 *     <column name="comments" type="longvarchar" />
 *     <column name="date_created" type="timestamp" />
 *     <column name="date_modified" type="timestamp" />
 *     <column name="author" type="varchar" size="255" />
 *     <column name="translated" type="integer" />
 *    </table>
 *    ...
 *  </database>
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version v1.0, last update on Fri Dec 24 16:58:58 EST 2004
 */
class sfMessageSource_SQLite extends sfMessageSource
{
    /**
     * The SQLite datasource, the filename of the database.
     *
     * @var string
     */
    protected $source;

    /**
     * Constructor.
     * Creates a new message source using SQLite.
     *
     * @see MessageSource::factory();
     *
     * @param string SQLite datasource, in PEAR's DB DSN format
     */
    public function __construct($source)
    {
        $dsn = parseDSN((string) $source);
        $this->source = $dsn['database'];
    }

    /**
     * Gets an array of messages for a particular catalogue and cultural variant.
     *
     * @param string the catalogue name + variant
     *
     * @return array translation messages
     */
    protected function &loadData($variant)
    {
        $variant = sqlite_escape_string($variant);

        $statement =
          "SELECT t.id, t.source, t.target, t.comments
        FROM trans_unit t, catalogue c
        WHERE c.cat_id =  t.cat_id
          AND c.name = '{$variant}'
        ORDER BY id ASC";

        $db = sqlite_open($this->source);
        $rs = sqlite_query($statement, $db);

        $result = [];

        while ($row = sqlite_fetch_array($rs, SQLITE_NUM)) {
            $source = $row[1];
            $result[$source][] = $row[2]; // target
            $result[$source][] = $row[0]; // id
            $result[$source][] = $row[3]; // comments
        }

        sqlite_close($db);

        return $result;
    }

    /**
     * Gets the last modified unix-time for this particular catalogue+variant.
     * We need to query the database to get the date_modified.
     *
     * @param string catalogue+variant
     *
     * @return int last modified in unix-time format
     */
    protected function getLastModified($source)
    {
        $source = sqlite_escape_string($source);

        $db = sqlite_open($this->source);

        $rs = sqlite_query("SELECT date_modified FROM catalogue WHERE name = '{$source}'", $db);

        $result = $rs ? intval(sqlite_fetch_single($rs)) : 0;

        sqlite_close($db);

        return $result;
    }

    /**
     * Checks if a particular catalogue+variant exists in the database.
     *
     * @param string catalogue+variant
     *
     * @return bool true if the catalogue+variant is in the database, false otherwise
     */
    protected function isValidSource($variant)
    {
        $variant = sqlite_escape_string($variant);
        $db = sqlite_open($this->source);
        $rs = sqlite_query("SELECT COUNT(*) FROM catalogue WHERE name = '{$variant}'", $db);
        $result = $rs && intval(sqlite_fetch_single($rs));
        sqlite_close($db);

        return $result;
    }

    /**
     * Gets all the variants of a particular catalogue.
     *
     * @param string catalogue name
     *
     * @return array list of all variants for this catalogue
     */
    protected function getCatalogueList($catalogue)
    {
        $variants = explode('_', $this->culture);

        $catalogues = [$catalogue];

        $variant = null;

        for ($i = 0, $max = count($variants); $i < $max; ++$i) {
            if (strlen($variants[$i]) > 0) {
                $variant .= ($variant) ? '_'.$variants[$i] : $variants[$i];
                $catalogues[] = $catalogue.'.'.$variant;
            }
        }

        return array_reverse($catalogues);
    }

    /**
     * Retrieves catalogue details, array($cat_id, $variant, $count).
     *
     * @param string catalogue
     *
     * @return array catalogue details, array($cat_id, $variant, $count)
     */
    protected function getCatalogueDetails($catalogue = 'messages')
    {
        if (empty($catalogue)) {
            $catalogue = 'messages';
        }

        $variant = $catalogue.'.'.$this->culture;

        $name = sqlite_escape_string($this->getSource($variant));

        $db = sqlite_open($this->source);

        $rs = sqlite_query("SELECT cat_id FROM catalogue WHERE name = '{$name}'", $db);

        if (sqlite_num_rows($rs) != 1) {
            return false;
        }

        $cat_id = intval(sqlite_fetch_single($rs));

        // first get the catalogue ID
        $rs = sqlite_query("SELECT count(msg_id) FROM trans_unit WHERE cat_id = {$cat_id}", $db);

        $count = intval(sqlite_fetch_single($rs));

        sqlite_close($db);

        return [$cat_id, $variant, $count];
    }

    /**
     * Updates the catalogue last modified time.
     *
     * @return bool true if updated, false otherwise
     */
    protected function updateCatalogueTime($cat_id, $variant, $db)
    {
        $time = time();

        $result = sqlite_query("UPDATE catalogue SET date_modified = {$time} WHERE cat_id = {$cat_id}", $db);

        if (!empty($this->cache)) {
            $this->cache->clean($variant, $this->culture);
        }

        return $result;
    }

    /**
     * Saves the list of untranslated blocks to the translation source.
     * If the translation was not found, you should add those
     * strings to the translation source via the <b>append()</b> method.
     *
     * @param string the catalogue to add to
     *
     * @return bool true if saved successfuly, false otherwise
     */
    public function save($catalogue = 'messages')
    {
        $messages = $this->untranslated;

        if (count($messages) <= 0) {
            return false;
        }

        $details = $this->getCatalogueDetails($catalogue);

        if ($details) {
            [$cat_id, $variant, $count] = $details;
        } else {
            return false;
        }

        if ($cat_id <= 0) {
            return false;
        }
        $inserted = 0;

        $db = sqlite_open($this->source);
        $time = time();

        foreach ($messages as $message) {
            $message = sqlite_escape_string($message);
            $statement = "INSERT INTO trans_unit (cat_id,id,source,date_added) VALUES ({$cat_id}, {$count},'{$message}',$time)";
            if (sqlite_query($statement, $db)) {
                ++$count;
                ++$inserted;
            }
        }
        if ($inserted > 0) {
            $this->updateCatalogueTime($cat_id, $variant, $db);
        }

        sqlite_close($db);

        return $inserted > 0;
    }

    /**
     * Updates the translation.
     *
     * @param string the source string
     * @param string the new translation string
     * @param string comments
     * @param string the catalogue of the translation
     *
     * @return bool true if translation was updated, false otherwise
     */
    public function update($text, $target, $comments, $catalogue = 'messages')
    {
        $details = $this->getCatalogueDetails($catalogue);
        if ($details) {
            [$cat_id, $variant, $count] = $details;
        } else {
            return false;
        }

        $comments = sqlite_escape_string($comments);
        $target = sqlite_escape_string($target);
        $text = sqlite_escape_string($text);

        $time = time();

        $db = sqlite_open($this->source);

        $statement = "UPDATE trans_unit SET target = '{$target}', comments = '{$comments}', date_modified = '{$time}' WHERE cat_id = {$cat_id} AND source = '{$text}'";

        $updated = false;

        if (sqlite_query($statement, $db)) {
            $updated = $this->updateCatalogueTime($cat_id, $variant, $db);
        }

        sqlite_close($db);

        return $updated;
    }

    /**
     * Deletes a particular message from the specified catalogue.
     *
     * @param string the source message to delete
     * @param string the catalogue to delete from
     *
     * @return bool true if deleted, false otherwise
     */
    public function delete($message, $catalogue = 'messages')
    {
        $details = $this->getCatalogueDetails($catalogue);
        if ($details) {
            [$cat_id, $variant, $count] = $details;
        } else {
            return false;
        }

        $db = sqlite_open($this->source);
        $text = sqlite_escape_string($message);

        $statement = "DELETE FROM trans_unit WHERE cat_id = {$cat_id} AND source = '{$message}'";
        $deleted = false;

        if (sqlite_query($statement, $db)) {
            $deleted = $this->updateCatalogueTime($cat_id, $variant, $db);
        }

        sqlite_close($db);

        return $deleted;
    }

    /**
     * Returns a list of catalogue as key and all it variants as value.
     *
     * @return array list of catalogues
     */
    public function catalogues()
    {
        $db = sqlite_open($this->source);
        $statement = 'SELECT name FROM catalogue ORDER BY name';
        $rs = sqlite_query($statement, $db);
        $result = [];
        while ($row = sqlite_fetch_array($rs, SQLITE_NUM)) {
            $details = explode('.', (string) $row[0]);
            if (!isset($details[1])) {
                $details[1] = null;
            }

            $result[] = $details;
        }
        sqlite_close($db);

        return $result;
    }
}
