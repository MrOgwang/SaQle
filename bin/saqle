#!/usr/bin/env php

<?php

require __DIR__ . '/../vendor/autoload.php';

use SaQle\Manage\Manage;
use SaQle\Config\AppConfig;

AppConfig::init()::load();

$argv[] = dirname(__FILE__);
(new Manage($argv))();

