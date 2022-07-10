<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!isset($sf_symfony_lib_dir))
{
  die("You must launch symfony command line with the symfony script\n");
}

// set magic_quotes_runtime to off
ini_set('magic_quotes_runtime', 'Off');

// force populating $argc and $argv in the case PHP does not automatically create them (fixes #2943)
$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];

// check if we are using an old project
if (file_exists('config/config.php') && !isset($sf_symfony_lib_dir))
{
  // allow only upgrading
  if (!in_array('upgrade', $argv))
  {
    echo "Please upgrade your project before launching any other symfony task\n";
    exit();
  }
}

// trap -V before pake
if (in_array('-V', $argv) || in_array('--version', $argv))
{
  printf("symfony version %s\n", pakeColor::colorize(trim(file_get_contents($sf_symfony_lib_dir.'/VERSION')), 'INFO'));
  exit(0);
}

if (count($argv) <= 1)
{
  $argv[] = '-T';
}

require_once($sf_symfony_lib_dir.'/config/sfConfig.class.php');

sfConfig::add(array(
  'sf_root_dir'         => getcwd(),
  'sf_symfony_lib_dir'  => $sf_symfony_lib_dir,
  'sf_symfony_data_dir' => $sf_symfony_data_dir,
));

// directory layout
include($sf_symfony_data_dir.'/config/constants.php');

// include path
set_include_path(
  sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
  sfConfig::get('sf_model_dir').PATH_SEPARATOR.
  sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
  get_include_path()
);

// register tasks
$dirs = array(
  sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'tasks'         => 'myPake*.php', // project tasks
  sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.'tasks' => 'sfPake*.php', // symfony tasks
  sfConfig::get('sf_root_dir').'/plugins/*/data/tasks'             => '*.php',       // plugin tasks
);
foreach ($dirs as $globDir => $name)
{
  if ($dirs = glob($globDir))
  {
    $tasks = pakeFinder::type('file')->ignore_version_control()->name($name)->in($dirs);
    foreach ($tasks as $task)
    {
      include_once($task);
    }
  }
}

// run task
pakeApp::get_instance()->run(null, null, false);

exit(0);
