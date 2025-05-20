<?php
namespace SaQle\Orm\Database\Services;

use Psr\Container\ContainerInterface;
use SaQle\Orm\Database\Attributes\DbContextOptions;

class ContextOptionsFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        return new DbContextOptions(
             name:     $kwargs['name'], 
             type:     $kwargs['type'], 
             port:     $kwargs['port'], 
             username: $kwargs['username'], 
             password: $kwargs['password']
         );
     }
}

