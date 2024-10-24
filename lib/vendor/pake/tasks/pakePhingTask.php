<?php

/**
 * @package    pake
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @license    see the LICENSE file included in the distribution
 * @version    SVN: $Id: pakePhingTask.class.php 4977 2007-09-05 09:14:45Z noel $
 */

class pakePhingTask
{
  public static function import_default_tasks()
  {
  }

  public static function call_phing($task, $target, $build_file = '', $options = [])
  {
    $args = [];
    foreach ($options as $key => $value)
    {
      $args[] = "-D$key=$value";
    }

    if ($build_file)
    {
      $args[] = '-f';
      $args[] = realpath($build_file);
    }

    if (!$task->is_verbose())
    {
      $args[] = '-q';
    }

    if (is_array($target))
    {
      $args = array_merge($args, $target);
    }
    else
    {
      $args[] = $target;
    }

    if (DIRECTORY_SEPARATOR != '\\' && (function_exists('posix_isatty') && @posix_isatty(pake_STDOUT())))
    {
      $args[] = '-logger';
      $args[] = 'phing.listener.AnsiColorLogger';
    }

    Phing::startup();
    Phing::setProperty('phing.home', getenv('PHING_HOME'));

    $m = new pakePhing();
    $m->execute($args);
    $m->runBuild();
  }
}
