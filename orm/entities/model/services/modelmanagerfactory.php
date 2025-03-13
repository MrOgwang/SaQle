<?php
namespace SaQle\Orm\Entities\Model\Services;

use Psr\Container\ContainerInterface;
use SaQle\Orm\Entities\Model\Manager\ModelManager;
use SaQle\Http\Request\Request;
use SaQle\Orm\Database\Trackers\DbContextTracker;

class ModelManagerFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        // You can access the container to get other dependencies
        // $dependency = $container->get(Dependency::class);

        // Create the object and pass the extra parameters
        return new ModelManager(
             $container->get(Request::class),
             $container->get(DbContextTracker::class)
        );
     }
}

?>