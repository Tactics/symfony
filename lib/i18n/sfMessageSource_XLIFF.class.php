<?php

/**
 * sfMessageSource_XLIFF class file.
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
 * @version    $Id: sfMessageSource_XLIFF.class.php 19777 2009-07-01 10:14:21Z fabien $
 */
/**
 * sfMessageSource_XLIFF class.
 *
 * Using XML XLIFF format as the message source for translation.
 * Details and example of XLIFF can be found in the following URLs.
 *
 * # http://www.opentag.com/xliff.htm
 * # http://www-106.ibm.com/developerworks/xml/library/x-localis2/
 *
 * See the MessageSource::factory() method to instantiate this class.
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version v1.0, last update on Fri Dec 24 16:18:44 EST 2004
 */
class sfMessageSource_XLIFF extends sfMessageSource
{
    /**
     * Message data filename extension.
     *
     * @var string
     */
    protected $dataExt = '.xml';

    /**
     * Separator between culture name and source.
     *
     * @var string
     */
    protected $dataSeparator = '.';

    /**
     * Constructor.
     *
     * @param string the directory where the messages are stored
     *
     * @see MessageSource::factory();
     */
    public function __construct($source)
    {
        $this->source = (string) $source;
    }

    /**
     * Loads the messages from a XLIFF file.
     *
     * @param string XLIFF file
     *
     * @return array of messages
     */
    protected function &loadData($filename)
    {
        libxml_use_internal_errors(true);
        if (!$xml = simplexml_load_file($filename)) {
            $error = false;

            return $error;
        }
        libxml_use_internal_errors(false);

        $translationUnit = $xml->xpath('//trans-unit');

        $translations = [];

        foreach ($translationUnit as $unit) {
            $source = (string) $unit->source;
            $translations[$source][] = (string) $unit->target;
            $translations[$source][] = (string) $unit['id'];
            $translations[$source][] = (string) $unit->note;
        }

        return $translations;
    }

    /**
     * Gets the last modified unix-time for this particular catalogue+variant.
     * Just use the file modified time.
     *
     * @param string catalogue+variant
     *
     * @return int last modified in unix-time format
     */
    protected function getLastModified($source)
    {
        return is_file($source) ? filemtime($source) : 0;
    }

    /**
     * Gets the XLIFF file for a specific message catalogue and cultural variant.
     *
     * @param string message catalogue
     *
     * @return string full path to the XLIFF file
     */
    protected function getSource($variant)
    {
        return $this->source.'/'.$variant;
    }

    /**
     * Determines if the XLIFF file source is valid.
     *
     * @param string XLIFF file
     *
     * @return bool true if valid, false otherwise
     */
    protected function isValidSource($source)
    {
        return is_file($source);
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
        $source = $catalogue.$this->dataExt;

        $catalogues = [$source];

        $variant = null;

        for ($i = 0, $max = count($variants); $i < $max; ++$i) {
            if (strlen($variants[$i]) > 0) {
                $variant .= $variant ? '_'.$variants[$i] : $variants[$i];
                $catalogues[] = $catalogue.$this->dataSeparator.$variant.$this->dataExt;
            }
        }

        $byDir = $this->getCatalogueByDir($catalogue);
        $catalogues = array_merge($byDir, array_reverse($catalogues));

        return $catalogues;
    }

    /**
     * Traverses through the directory structure to find the catalogues.
     * This should only be called by getCatalogueList().
     *
     * @param string a particular catalogue
     *
     * @return array a list of catalogues
     *
     * @see getCatalogueList()
     */
    protected function getCatalogueByDir($catalogue)
    {
        $variants = explode('_', $this->culture);
        $catalogues = [];

        $variant = null;

        for ($i = 0, $max = count($variants); $i < $max; ++$i) {
            if (strlen($variants[$i]) > 0) {
                $variant .= $variant ? '_'.$variants[$i] : $variants[$i];
                $catalogues[] = $variant.'/'.$catalogue.$this->dataExt;
            }
        }

        return array_reverse($catalogues);
    }

    /**
     * Returns a list of catalogue and its culture ID.
     * E.g. array('messages', 'en_AU').
     *
     * @return array list of catalogues
     *
     * @see getCatalogues()
     */
    public function catalogues()
    {
        return $this->getCatalogues();
    }

    /**
     * Returns a list of catalogue and its culture ID. This takes care
     * of directory structures.
     * E.g. array('messages', 'en_AU').
     *
     * @return array list of catalogues
     */
    protected function getCatalogues($dir = null, $variant = null)
    {
        $dir = $dir ?: $this->source;
        $files = scandir($dir);

        $catalogue = [];

        foreach ($files as $file) {
            if (is_dir($dir.'/'.$file) && preg_match('/^[a-z]{2}(_[A-Z]{2,3})?$/', $file)) {
                $catalogue = array_merge($catalogue, $this->getCatalogues($dir.'/'.$file, $file));
            }

            $pos = strpos($file, $this->dataExt);
            if ($pos > 0 && substr($file, -1 * strlen($this->dataExt)) == $this->dataExt) {
                $name = substr($file, 0, $pos);
                $dot = strrpos($name, $this->dataSeparator);
                $culture = $variant;
                $cat = $name;
                if (is_int($dot)) {
                    $culture = substr($name, $dot + 1, strlen($name));
                    $cat = substr($name, 0, $dot);
                }
                $details[0] = $cat;
                $details[1] = $culture;

                $catalogue[] = $details;
            }
        }
        sort($catalogue);

        return $catalogue;
    }

    /**
     * Gets the variant for a catalogue depending on the current culture.
     *
     * @param string catalogue
     *
     * @return string the variant
     *
     * @see save()
     * @see update()
     * @see delete()
     */
    protected function getVariants($catalogue = 'messages')
    {
        if (is_null($catalogue)) {
            $catalogue = 'messages';
        }

        foreach ($this->getCatalogueList($catalogue) as $variant) {
            $file = $this->getSource($variant);
            if (is_file($file)) {
                return [$variant, $file];
            }
        }

        return false;
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

        $variants = $this->getVariants($catalogue);
        if ($variants) {
            [$variant, $filename] = $variants;
        } else {
            return false;
        }

        if (is_writable($filename) == false) {
            throw new sfException(sprintf('Unable to save to file %s, file must be writable.', $filename));
        }

        // create a new dom, import the existing xml
        $dom = new DOMDocument();
        $dom->load($filename);

        // find the body element
        $xpath = new DOMXPath($dom);
        $body = $xpath->query('//body')->item(0);

        // find the biggest "id" used
        $lastNodes = $xpath->query('//trans-unit[not(@id <= preceding-sibling::trans-unit/@id) and not(@id <= following-sibling::trans-unit/@id)]');
        if (null !== $last = $lastNodes->item(0)) {
            $count = intval($last->getAttribute('id'));
        } else {
            $count = 0;
        }

        // for each message add it to the XML file using DOM
        foreach ($messages as $message) {
            $unit = $dom->createElement('trans-unit');
            $unit->setAttribute('id', ++$count);

            $source = $dom->createElement('source');
            $source->appendChild($dom->createTextNode($message));
            $target = $dom->createElement('target');
            $target->appendChild($dom->createTextNode(''));

            $unit->appendChild($dom->createTextNode("\n"));
            $unit->appendChild($source);
            $unit->appendChild($dom->createTextNode("\n"));
            $unit->appendChild($target);
            $unit->appendChild($dom->createTextNode("\n"));

            $body->appendChild($dom->createTextNode("\n"));
            $body->appendChild($unit);
            $body->appendChild($dom->createTextNode("\n"));
        }

        $fileNode = $xpath->query('//file')->item(0);
        $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

        // save it and clear the cache for this variant
        $dom->save($filename);
        if (!empty($this->cache)) {
            $this->cache->clean($variant, $this->culture);
        }

        return true;
    }

    /**
     * Updates the translation.
     *
     * @param string the source string
     * @param string the new translation string
     * @param string comments
     * @param string the catalogue to save to
     *
     * @return bool true if translation was updated, false otherwise
     */
    public function update($text, $target, $comments, $catalogue = 'messages')
    {
        $variants = $this->getVariants($catalogue);
        if ($variants) {
            [$variant, $filename] = $variants;
        } else {
            return false;
        }

        if (is_writable($filename) == false) {
            throw new sfException(sprintf('Unable to update file %s, file must be writable.', $filename));
        }

        // create a new dom, import the existing xml
        $dom = new DOMDocument();
        $dom->load($filename);

        // find the body element
        $xpath = new DOMXPath($dom);
        $units = $xpath->query('//trans-unit');

        // for each of the existin units
        foreach ($units as $unit) {
            $found = false;
            $targetted = false;
            $commented = false;

            // in each unit, need to find the source, target and comment nodes
            // it will assume that the source is before the target.
            foreach ($unit->childNodes as $node) {
                // source node
                if ($node->nodeName == 'source' && $node->firstChild->wholeText == $text) {
                    $found = true;
                }

                // found source, get the target and notes
                if ($found) {
                    // set the new translated string
                    if ($node->nodeName == 'target') {
                        $node->nodeValue = $target;
                        $targetted = true;
                    }

                    // set the notes
                    if (!empty($comments) && $node->nodeName == 'note') {
                        $node->nodeValue = $comments;
                        $commented = true;
                    }
                }
            }

            // append a target
            if ($found && !$targetted) {
                $targetNode = $dom->createElement('target');
                $targetNode->appendChild($dom->createTextNode($target));
                $unit->appendChild($targetNode);
            }

            // append a note
            if ($found && !$commented && !empty($comments)) {
                $commentsNode = $dom->createElement('note');
                $commentsNode->appendChild($dom->createTextNode($comments));
                $unit->appendChild($commentsNode);
            }

            // finished searching
            if ($found) {
                break;
            }
        }

        $fileNode = $xpath->query('//file')->item(0);
        $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

        if ($dom->save($filename) > 0) {
            if (!empty($this->cache)) {
                $this->cache->clean($variant, $this->culture);
            }

            return true;
        }

        return false;
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
        $variants = $this->getVariants($catalogue);
        if ($variants) {
            [$variant, $filename] = $variants;
        } else {
            return false;
        }

        if (is_writable($filename) == false) {
            throw new sfException(sprintf('Unable to modify file %s, file must be writable.', $filename));
        }

        // create a new dom, import the existing xml
        $dom = new DOMDocument();
        $dom->load($filename);

        // find the body element
        $xpath = new DOMXPath($dom);
        $units = $xpath->query('//trans-unit');

        // for each of the existin units
        foreach ($units as $unit) {
            // in each unit, need to find the source, target and comment nodes
            // it will assume that the source is before the target.
            foreach ($unit->childNodes as $node) {
                // source node
                if ($node->nodeName == 'source' && $node->firstChild->wholeText == $message) {
                    // we found it, remove and save the xml file.
                    $unit->parentNode->removeChild($unit);

                    $fileNode = $xpath->query('//file')->item(0);
                    $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

                    if ($dom->save($filename) > 0) {
                        if (!empty($this->cache)) {
                            $this->cache->clean($variant, $this->culture);
                        }

                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }
}
