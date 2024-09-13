<?php

/**
 * @package    pake
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @license    see the LICENSE file included in the distribution
 * @version    SVN: $Id: pakeColor.class.php 2990 2006-12-09 11:10:59Z fabien $
 */

/**
 *
 * main pake class.
 *
 * This class is a singleton.
 *
 * @package    pake
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @license    see the LICENSE file included in the distribution
 * @version    SVN: $Id: pakeColor.class.php 2990 2006-12-09 11:10:59Z fabien $
 */
class pakeColor
{
  static public $styles = [];

  static function style($name, $options = [])
  {
    self::$styles[$name] = $options;
  }

  static function colorize($text, $parameters, $stream)
  {
      $stream = $stream ?: pake_STDOUT();
    // disable colors if not supported (windows or non tty console)
    if (DIRECTORY_SEPARATOR == '\\' || !function_exists('posix_isatty') || !@posix_isatty($stream))
    {
      return $text;
    }

    static $options    = ['bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8];
    static $foreground = ['black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37];
    static $background = ['black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47];

    if (!is_array($parameters) && isset(self::$styles[$parameters]))
    {
      $parameters = self::$styles[$parameters];
    }

    $codes = [];
    if (isset($parameters['fg']))
    {
      $codes[] = $foreground[$parameters['fg']];
    }
    if (isset($parameters['bg']))
    {
      $codes[] = $background[$parameters['bg']];
    }
    foreach ($options as $option => $value)
    {
      if (isset($parameters[$option]) && $parameters[$option])
      {
        $codes[] = $value;
      }
    }

    return "\033[".implode(';', $codes).'m'.$text."\033[0m";
  }
}

pakeColor::style('ERROR',    ['bg' => 'red', 'fg' => 'white', 'bold' => true]);
pakeColor::style('INFO',     ['fg' => 'green', 'bold' => true]);
pakeColor::style('COMMENT',  ['fg' => 'yellow']);
