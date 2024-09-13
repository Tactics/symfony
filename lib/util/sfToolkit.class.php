<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfToolkit provides basic utility methods.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfToolkit.class.php 19216 2009-06-13 06:42:00Z fabien $
 */
class sfToolkit
{
    /**
     * Extract the class or interface name from filename.
     *
     * @param string a filename
     *
     * @return string a class or interface name, if one can be extracted, otherwise null
     */
    public static function extractClassName($filename)
    {
        $retval = null;

        if (self::isPathAbsolute($filename)) {
            $filename = basename((string) $filename);
        }

        $pattern = '/(.*?)\.(class|interface)\.php/i';

        if (preg_match($pattern, (string) $filename, $match)) {
            $retval = $match[1];
        }

        return $retval;
    }

    /**
     * Clear all files in a given directory.
     *
     * @param  string an absolute filesystem path to a directory
     *
     * @return void
     */
    public static function clearDirectory($directory)
    {
        if (!is_dir($directory)) {
            return;
        }

        // open a file point to the cache dir
        $fp = opendir($directory);

        // ignore names
        $ignore = ['.', '..', 'CVS', '.svn'];

        while (($file = readdir($fp)) !== false) {
            if (!in_array($file, $ignore)) {
                if (is_link($directory.'/'.$file)) {
                    // delete symlink
                    unlink($directory.'/'.$file);
                } elseif (is_dir($directory.'/'.$file)) {
                    // recurse through directory
                    self::clearDirectory($directory.'/'.$file);

                    // delete the directory
                    rmdir($directory.'/'.$file);
                } else {
                    // delete the file
                    unlink($directory.'/'.$file);
                }
            }
        }

        // close file pointer
        closedir($fp);
    }

    /**
     * Clear all files and directories corresponding to a glob pattern.
     *
     * @param  string an absolute filesystem pattern
     *
     * @return void
     */
    public static function clearGlob($pattern)
    {
        $files = glob($pattern);

        // order is important when removing directories
        sort($files);

        foreach ($files as $file) {
            if (is_dir($file)) {
                // delete directory
                self::clearDirectory($file);
            } else {
                // delete file
                unlink($file);
            }
        }
    }

    /**
     * Determine if a filesystem path is absolute.
     *
     * @param path a filesystem path
     *
     * @return bool true, if the path is absolute, otherwise false
     */
    public static function isPathAbsolute($path)
    {
        if ($path[0] == '/' || $path[0] == '\\'
            || (strlen((string) $path) > 3 && ctype_alpha((string) $path[0])
             && $path[1] == ':'
             && ($path[2] == '\\' || $path[2] == '/')
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a lock file is present.
     *
     * @param int a max amount of life time for the lock file
     *
     * @return bool true, if the lock file is present, otherwise false
     */
    public static function hasLockFile($lockFile, $maxLockFileLifeTime = 0)
    {
        $isLocked = false;
        if (is_readable($lockFile) && ($last_access = fileatime($lockFile))) {
            $now = time();
            $timeDiff = $now - $last_access;

            if (!$maxLockFileLifeTime || $timeDiff < $maxLockFileLifeTime) {
                $isLocked = true;
            } else {
                $isLocked = @unlink($lockFile) ? false : true;
            }
        }

        return $isLocked;
    }

    public static function stripComments($source)
    {
        if (!sfConfig::get('sf_strip_comments', true) || !function_exists('token_get_all')) {
            return $source;
        }

        $ignore = [T_COMMENT => true, T_DOC_COMMENT => true];
        $output = '';

        foreach (token_get_all($source) as $token) {
            // array
            if (isset($token[1])) {
                // no action on comments
                if (!isset($ignore[$token[0]])) {
                    // anything else -> output "as is"
                    $output .= $token[1];
                }
            } else {
                // simple 1-character token
                $output .= $token;
            }
        }

        return $output;
    }

    public static function stripslashesDeep($value)
    {
        return is_array($value) ? array_map(['sfToolkit', 'stripslashesDeep'], $value) : stripslashes((string) $value);
    }

    // code from php at moechofe dot com (array_merge comment on php.net)
    /*
     * array arrayDeepMerge ( array array1 [, array array2 [, array ...]] )
     *
     * Like array_merge
     *
     *  arrayDeepMerge() merges the elements of one or more arrays together so
     * that the values of one are appended to the end of the previous one. It
     * returns the resulting array.
     *  If the input arrays have the same string keys, then the later value for
     * that key will overwrite the previous one. If, however, the arrays contain
     * numeric keys, the later value will not overwrite the original value, but
     * will be appended.
     *  If only one array is given and the array is numerically indexed, the keys
     * get reindexed in a continuous way.
     *
     * Different from array_merge
     *  If string keys have arrays for values, these arrays will merge recursively.
     */
    public static function arrayDeepMerge()
    {
        switch (func_num_args()) {
            case 0:
                return false;
            case 1:
                return func_get_arg(0);
            case 2:
                $args = func_get_args();
                $args[2] = [];
                if (is_array($args[0]) && is_array($args[1])) {
                    foreach (array_unique(array_merge(array_keys($args[0]), array_keys($args[1]))) as $key) {
                        $isKey0 = array_key_exists($key, $args[0]);
                        $isKey1 = array_key_exists($key, $args[1]);
                        if ($isKey0 && $isKey1 && is_array($args[0][$key]) && is_array($args[1][$key])) {
                            $args[2][$key] = self::arrayDeepMerge($args[0][$key], $args[1][$key]);
                        } elseif ($isKey0 && $isKey1) {
                            $args[2][$key] = $args[1][$key];
                        } elseif (!$isKey1) {
                            $args[2][$key] = $args[0][$key];
                        } elseif (!$isKey0) {
                            $args[2][$key] = $args[1][$key];
                        }
                    }

                    return $args[2];
                } else {
                    return $args[1];
                }
                // no break
            default:
                $args = func_get_args();
                $args[1] = sfToolkit::arrayDeepMerge($args[0], $args[1]);
                array_shift($args);

                return call_user_func_array(['sfToolkit', 'arrayDeepMerge'], $args);
                break;
        }
    }

    public static function stringToArray($string)
    {
        preg_match_all('/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', (string) $string, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $val) {
            $attributes[$val[1]] = self::literalize($val[3]);
        }

        return $attributes;
    }

    /**
     * Finds the type of the passed value, returns the value as the new type.
     *
     * @param  string
     */
    public static function literalize($value, $quoted = false)
    {
        // lowercase our value for comparison
        $value = trim((string) $value);
        $lvalue = strtolower($value);

        if (in_array($lvalue, ['null', '~', ''])) {
            $value = null;
        } elseif (in_array($lvalue, ['true', 'on', '+', 'yes'])) {
            $value = true;
        } elseif (in_array($lvalue, ['false', 'off', '-', 'no'])) {
            $value = false;
        } elseif (ctype_digit($value)) {
            $value = (int) $value;
        } elseif (is_numeric($value)) {
            $value = (float) $value;
        } else {
            $value = self::replaceConstants($value);
            if ($quoted) {
                $value = '\''.str_replace('\'', '\\\'', $value).'\'';
            }
        }

        return $value;
    }

    /**
     * Replaces constant identifiers in a scalar value.
     *
     * @param string the value to perform the replacement on
     *
     * @return string the value with substitutions made
     */
    public static function replaceConstants($value)
    {
        return is_string($value) ? preg_replace_callback('/%(.+?)%/', fn ($v) => sfConfig::has(strtolower((string) $v[1])) ? sfConfig::get(strtolower((string) $v[1])) : "%{$v[1]}%", $value) : $value;
    }

    /**
     * Returns subject replaced with regular expression matchs.
     *
     * @param mixed subject to search
     * @param array array of search => replace pairs
     *
     * @return string|string[]|null
     */
    public static function pregtr($search, $replacePairs)
    {
        return $search ? preg_replace(array_keys($replacePairs), array_values($replacePairs), $search) : $search;
    }

    /**
     * Returns subject replaced with regular expression matches.
     * This function accepts callback replacements only. Use sfToolkit::pregtr to do simple preg_replace.
     *
     * @param mixed $search       subject to search
     * @param array $replacePairs array of search => replace callback pairs
     *
     * @return mixed|string|string[]|null
     */
    public static function pregtrcb(mixed $search, $replacePairs)
    {
        foreach ($replacePairs as $pattern => $callback) {
            $search = preg_replace_callback(
                $pattern,
                $callback,
                $search
            );
        }

        return $search;
    }

    public static function isArrayValuesEmpty($array)
    {
        static $isEmpty = true;
        foreach ($array as $value) {
            $isEmpty = (is_array($value)) ? self::isArrayValuesEmpty($value) : (strlen((string) $value) == 0);
            if (!$isEmpty) {
                break;
            }
        }

        return $isEmpty;
    }

    /**
     * Checks if a string is an utf8.
     *
     * Yi Stone Li<yili@yahoo-inc.com>
     * Copyright (c) 2007 Yahoo! Inc. All rights reserved.
     * Licensed under the BSD open source license
     *
     * @param string
     *
     * @return bool true if $string is valid UTF-8 and false otherwise
     */
    public static function isUTF8($string)
    {
        for ($idx = 0, $strlen = strlen((string) $string); $idx < $strlen; ++$idx) {
            $byte = ord($string[$idx]);

            if ($byte & 0x80) {
                if (($byte & 0xE0) == 0xC0) {
                    // 2 byte char
                    $bytes_remaining = 1;
                } elseif (($byte & 0xF0) == 0xE0) {
                    // 3 byte char
                    $bytes_remaining = 2;
                } elseif (($byte & 0xF8) == 0xF0) {
                    // 4 byte char
                    $bytes_remaining = 3;
                } else {
                    return false;
                }

                if ($idx + $bytes_remaining >= $strlen) {
                    return false;
                }

                while ($bytes_remaining--) {
                    if ((ord($string[++$idx]) & 0xC0) != 0x80) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function &getArrayValueForPathByRef(&$values, $name, $default = null)
    {
        if (false !== ($offset = strpos((string) $name, '['))) {
            if (isset($values[substr((string) $name, 0, $offset)])) {
                $array = &$values[substr((string) $name, 0, $offset)];

                while ($pos = strpos((string) $name, '[', $offset)) {
                    $end = strpos((string) $name, ']', $pos);
                    if ($end == $pos + 1) {
                        // reached a []
                        break;
                    } elseif (!isset($array[substr((string) $name, $pos + 1, $end - $pos - 1)])) {
                        return $default;
                    } elseif (is_array($array)) {
                        $array = &$array[substr((string) $name, $pos + 1, $end - $pos - 1)];
                        $offset = $end;
                    } else {
                        return $default;
                    }
                }

                return $array;
            }
        }

        return $default;
    }

    public static function getArrayValueForPath($values, $name, $default = null)
    {
        if (false !== ($offset = strpos((string) $name, '['))) {
            if (isset($values[substr((string) $name, 0, $offset)])) {
                $array = $values[substr((string) $name, 0, $offset)];

                while ($pos = strpos((string) $name, '[', $offset)) {
                    $end = strpos((string) $name, ']', $pos);
                    if ($end == $pos + 1) {
                        // reached a []
                        break;
                    } elseif (!isset($array[substr((string) $name, $pos + 1, $end - $pos - 1)])) {
                        return $default;
                    } elseif (is_array($array)) {
                        $array = $array[substr((string) $name, $pos + 1, $end - $pos - 1)];
                        $offset = $end;
                    } else {
                        return $default;
                    }
                }

                return $array;
            }
        }

        return $default;
    }

    public static function getPhpCli()
    {
        $path = getenv('PATH') ?: getenv('Path');
        $suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : ['.exe', '.bat', '.cmd', '.com']) : [''];
        foreach (['php5', 'php'] as $phpCli) {
            foreach ($suffixes as $suffix) {
                foreach (explode(PATH_SEPARATOR, $path) as $dir) {
                    $file = $dir.DIRECTORY_SEPARATOR.$phpCli.$suffix;
                    if (is_executable($file)) {
                        return $file;
                    }
                }
            }
        }

        throw new sfException('Unable to find PHP executable');
    }

    /**
     * From PEAR System.php.
     *
     * LICENSE: This source file is subject to version 3.0 of the PHP license
     * that is available through the world-wide-web at the following URI:
     * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
     * the PHP License and are unable to obtain it through the web, please
     * send a note to license@php.net so we can mail you a copy immediately.
     *
     * @author     Tomas V.V.Cox <cox@idecnet.com>
     * @copyright  1997-2006 The PHP Group
     * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
     */
    public static function getTmpDir()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            if ($var = $_ENV['TEMP'] ?? getenv('TEMP')) {
                return $var;
            }
            if ($var = $_ENV['TMP'] ?? getenv('TMP')) {
                return $var;
            }
            if ($var = $_ENV['windir'] ?? getenv('windir')) {
                return $var;
            }

            return getenv('SystemRoot').'\temp';
        }

        if ($var = $_ENV['TMPDIR'] ?? getenv('TMPDIR')) {
            return $var;
        }

        return '/tmp';
    }
}
