<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfInflector.class.php 13894 2008-12-09 22:31:49Z FabianLange $
 */
class sfInflector
{
    /**
     * Returns a camelized string from a lower case and underscored string by replaceing slash with
     * double-colon and upper-casing each letter preceded by an underscore.
     *
     * @param string string to camelize
     *
     * @return string camelized string
     */
    public static function camelize($lower_case_and_underscored_word)
    {
        $tmp = $lower_case_and_underscored_word;
        //    $tmp = sfToolkit::pregtr($tmp, array('#/(.?)#'    => "'::'.strtoupper('\\1')",
        //                                         '/(^|_)(.)/' => "strtoupper('\\2')"));

        $tmp = sfToolkit::pregtrcb(
            $tmp,
            ['#/(.?)#' => fn ($matches) => '::'.strtoupper((string) $matches[1]), '/(^|_|-)+(.)/' => fn ($matches) => strtoupper((string) $matches[2])]
        );

        return $tmp;
    }

    /**
     * Returns an underscore-syntaxed version or the CamelCased string.
     *
     * @param string string to underscore
     *
     * @return string underscored string
     */
    public static function underscore($camel_cased_word)
    {
        $tmp = $camel_cased_word;
        $tmp = str_replace('::', '/', $tmp);
        $tmp = sfToolkit::pregtr($tmp, ['/([A-Z]+)([A-Z][a-z])/' => '\\1_\\2', '/([a-z\d])([A-Z])/' => '\\1_\\2']);

        return strtolower($tmp);
    }

    /**
     * Returns classname::module with classname:: stripped off.
     *
     * @param string classname and module pair
     *
     * @return string module name
     */
    public static function demodulize($class_name_in_module)
    {
        return $class_name_in_module ? preg_replace('/^.*::/', '', $class_name_in_module) : $class_name_in_module;
    }

    /**
     * Returns classname in underscored form, with "_id" tacked on at the end.
     * This is for use in dealing with foreign keys in the database.
     *
     * @param string class name
     * @param bool seperate with underscore
     *
     * @return strong Foreign key
     */
    public static function foreign_key($class_name, $separate_class_name_and_id_with_underscore = true)
    {
        return sfInflector::underscore(sfInflector::demodulize($class_name)).($separate_class_name_and_id_with_underscore ? '_id' : 'id');
    }

    /**
     * Returns corresponding table name for given classname.
     *
     * @param string name of class to get database table name for
     *
     * @return string name of the databse table for given class
     */
    public static function tableize($class_name)
    {
        return sfInflector::underscore($class_name);
    }

    /**
     * Returns model class name for given database table.
     *
     * @param string table name
     *
     * @return string classified table name
     */
    public static function classify($table_name)
    {
        return sfInflector::camelize($table_name);
    }

    /**
     * Returns a human-readable string from a lower case and underscored word by replacing underscores
     * with a space, and by upper-casing the initial characters.
     *
     * @param string string to make more readable
     *
     * @return string human-readable string
     */
    public static function humanize($lower_case_and_underscored_word)
    {
        if (str_ends_with((string) $lower_case_and_underscored_word, '_id')) {
            $lower_case_and_underscored_word = substr((string) $lower_case_and_underscored_word, 0, -3);
        }

        return ucfirst(str_replace('_', ' ', $lower_case_and_underscored_word));
    }
}
