<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Container\Container;

abstract class ServiceProvider {
     abstract public function register(Container $container): void;
}

?>
