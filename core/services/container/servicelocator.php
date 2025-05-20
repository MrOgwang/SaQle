<?php
namespace SaQle\Core\Services\Container;

use SaQle\Core\Services\IService;

abstract class ServiceLocator implements IService{
     abstract public function register(Container $container): void;
}


