<?php
namespace SaQle\Dao\Model\Services;

use Psr\Container\ContainerInterface;
use SaQle\Dao\Model\Manager\ModelManager;
use SaQle\Http\Request\Request;
use SaQle\Dao\Filter\Manager\FilterManager;
use SaQle\Dao\Model\ModelCollection;
use SaQle\Dao\Formatter\DataFormatter;
use SaQle\Dao\Join\Manager\JoinManager;
use SaQle\Dao\Limit\Manager\LimitManager;
use SaQle\Dao\DbContext\Trackers\DbContextTracker;
use SaQle\Dao\Select\Manager\SelectManager;
use SaQle\Security\Security;
use SaQle\Dao\Order\Manager\OrderManager;

class ModelManagerFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        // You can access the container to get other dependencies
        // $dependency = $container->get(Dependency::class);

        // Create the object and pass the extra parameters
        return new ModelManager(
                $container->get(Request::class),
                $container->get(FilterManager::class),
                $container->get(DbContextTracker::class),
                $container->get(JoinManager::class),
                $container->get(LimitManager::class),
                $container->get(OrderManager::class),
                $container->get(SelectManager::class),
                $container->get(DataFormatter::class ),
                $kwargs['connection']
        );
     }
}

?>