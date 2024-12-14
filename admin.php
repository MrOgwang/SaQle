<?php

//require the autoloader for saqle
require_once 'autoloader.php';
SaQle\Autoloader::register(function($class){
     $class = strtolower(str_replace("\\", "/", str_replace("saqle/", "", strtolower($class))));
     require $class.".php";
});

use SaQle\Manage\Manage;
use SaQle\Config\AppConfig;

AppConfig::init()::load();

$argv[] = dirname(__FILE__);
(new Manage($argv))();
?>