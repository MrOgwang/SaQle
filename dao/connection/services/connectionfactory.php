<?php
namespace SaQle\Dao\Connection\Services;

use Psr\Container\ContainerInterface;
use SaQle\Dao\Connection\Connection;

class ConnectionFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        // You can access the container to get other dependencies
        // $dependency = $container->get(Dependency::class);

        // Create the object and pass the extra parameters
        return new Connection(context: $kwargs['context']);
     }
}

?>