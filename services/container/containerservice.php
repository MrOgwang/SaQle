<?php
namespace SaQle\Services\Container;

use SaQle\Orm\Database\Services\ContextOptionsFactory;
use SaQle\Orm\Connection\Services\ConnectionFactory;
use SaQle\Orm\Entities\Model\Services\ModelManagerFactory;
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
     * Create ModelManager object from context class
     * */
    public function createContextModelManager($context_class){
         $container_service = $this->container->get(ContainerService::class);
         $dbcontextoptions  = $container_service->createDbContextOptions(...DB_CONTEXT_CLASSES[$context_class]);
         $connection        = $container_service->createConnection(...['ctx' => $context_class]);
         $modelmanager      = $container_service->createModelManager(...['connection' => $connection]);
         return $modelmanager;
    }

    /**
     * Create DbContextObject
     * */
    public function createDbContext($context_class){
         $container_service = $this->container->get(ContainerService::class);
         $dbcontextoptions  = $container_service->createDbContextOptions(...DB_CONTEXT_CLASSES[$context_class]);
         $connection        = $container_service->createConnection(...['ctx' => $context_class]);
         $modelmanager      = $container_service->createModelManager(...['connection' => $connection]);
         return new $context_class($modelmanager);
    }

    /**
     * Create MakeMigrations object
     * */
    public function createMakeMigrations(...$options){

    }

    /**
     * Create MigrationsManager
     * */
    public function createMigrationsManager(...$options){

    }

    /**
     * Create ContextManager
     * */
    public function createContextManager(...$options){

    }
}

?>
