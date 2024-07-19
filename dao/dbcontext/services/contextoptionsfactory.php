<?php
namespace SaQle\Dao\DbContext\Services;

use Psr\Container\ContainerInterface;
use SaQle\Dao\DbContext\Attributes\DbContextOptions;

class ContextOptionsFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        // You can access the container to get other dependencies
        // $dependency = $container->get(Dependency::class);

        // Create the object and pass the extra parameters
        return new DbContextOptions(
             name:     $kwargs['name'], 
             type:     $kwargs['type'], 
             port:     $kwargs['port'], 
             username: $kwargs['username'], 
             password: $kwargs['password']
         );
     }
}

?>