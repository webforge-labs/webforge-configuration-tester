<?php
/**
 * Bootstrap and Autoload whole application
 *
 * you can use this file to bootstrap for tests or bootstrap for scripts / others
 */
$composerAutoLoader = require 'vendor/autoload.php';

$GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);
//$GLOBALS['env']['container'] = $container = new Webforge\Setup\BootContainer($GLOBALS['env']['root']);
//$container->setAutoLoader($composerAutoLoader);
