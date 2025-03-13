<?php
namespace SaQle\Orm\Connection\Services;

use Psr\Container\ContainerInterface;
use SaQle\Orm\Connection\Connection;
use SaQle\Services\Container\{Cf, ContainerService};

class ConnectionFactory{
     public function __invoke(ContainerInterface $container, ...$kwargs){
        $params = DB_CONTEXT_CLASSES[$kwargs['ctx']];
        if(isset($kwargs['without_db'])){
            $params['name'] = "";
        }
        return Connection::make(Cf::create(ContainerService::class)->createDbContextOptions(...$params));
     }
}

?>