<?php
namespace SaQle\Services\Container;

use SaQle\Dao\DbContext\Services\ContextOptionsFactory;
use SaQle\Dao\Connection\Services\ConnectionFactory;
use SaQle\Dao\Model\Services\ModelManagerFactory;
use Psr\Container\ContainerInterface;

class ContainerService{
    private $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    /**
     * Create DbContextOptions object.
     * */
    public function createDbContextOptions(...$options){
        $factory = new ContextOptionsFactory();
        return $factory($this->container, ...$options);
    }

    /**
     * Create Connection object
     * */
    public function createConnection(...$options){
        $factory = new ConnectionFactory();
        return $factory($this->container, ...$options);
    }

    /**
     * Create ModelManager object
     * */
    public function createModelManager(...$options){
        $factory = new ModelManagerFactory();
        return $factory($this->container, ...$options);
    }

    /**
     * Create DbContextObject
     * */
    public function createDbContext($context_class){
         $container_service = $this->container->get(ContainerService::class);
         $dbcontextoptions  = $container_service->createDbContextOptions(...DB_CONTEXT_CLASSES[$context_class]);
         $connection        = $container_service->createConnection(...['context' => $dbcontextoptions]);
         $modelmanager      = $container_service->createModelManager(...['connection' => $connection]);
         return new $context_class($modelmanager);
    }
}

?>
