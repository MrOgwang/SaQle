<?php
      /**
       * Dependency injection management definitions
       * */
      use Psr\Container\ContainerInterface;
      use function DI\factory;
      use function DI\create;
      use Booibo\Apps\Account\Data\AccountsDbContext;
      use SaQle\Dao\Model\Manager\ModelManager;
      use SaQle\Dao\DbContext\DbTypes;
      use SaQle\Dao\DbContext\DbPorts;
      use SaQle\Dao\DbContext\Attributes\DbContextOptions;
      use SaQle\Dao\DbContext\Trackers\DbContextTracker;
      
      use SaQle\Dao\Filter\Aggregator\Aggregator;
      use SaQle\Dao\Filter\Translator\Translator;
      use SaQle\Dao\Filter\Parser\Parser;

      use SaQle\Dao\Filter\Interfaces\IFilterManager;
      use SaQle\Dao\Filter\Manager\FilterManager;

      use SaQle\Dao\Order\Interfaces\IOrderManager;
      use SaQle\Dao\Order\Manager\OrderManager;

      use SaQle\Dao\Connection\Connection;
      use SaQle\Dao\Formatter\DataFormatter;

      use SaQle\Dao\Join\Interfaces\IJoinManager;
      use SaQle\Dao\Join\Manager\JoinManager;

      use SaQle\Dao\Limit\Interfaces\ILimitManager;
      use SaQle\Dao\Limit\Manager\LimitManager;
      
      use SaQle\Dao\Select\Interfaces\ISelectManager;
      use SaQle\Dao\Select\Manager\SelectManager;

      use SaQle\Dao\Group\Interfaces\IGroupManager;
      use SaQle\Dao\Group\Manager\GroupManager;

      use SaQle\Security\Security;
      use SaQle\Http\Request\Request;
      use SaQle\Services\Container\ContainerService;
      use SaQle\Migration\Managers\{ContextManager, Manager};
      use SaQle\Migration\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, MakeThroughs, SeedDatabase, MakeSuperuser, StartApps, StartProject};

	 return [
             Request::class => function(ContainerInterface $c){
         	       return Request::init();
	       },
	       'request' => function(ContainerInterface $c){
         	       return Request::init();
	       },
             DbContextTracker::class => function(ContainerInterface $c){
         	       return new DbContextTracker();
	       },
             DbContextOptions::class => function(ContainerInterface $c){
         	       return new DbContextOptions(
         	      	 name:     DATABASE_NAME, 
         	      	 type:     DbTypes::MYSQL, 
         	      	 port:     DbPorts::MYSQL, 
         	      	 username: DATABASE_USER, 
         	      	 password: DATABASE_PASSWORD
         	       );
	       },
	       Connection::class => function(ContainerInterface $c){
	     	       return new Connection($c->get(DbContextOptions::class));
	       },
	       Parser::class => function(ContainerInterface $c){
	     	       return new Parser();
	       },
	       Translator::class => function(ContainerInterface $c){
	     	       return new Translator();
	       },
	       Aggregator::class => function(ContainerInterface $c){
	     	       return new Aggregator();
	       },

	       //inject filter manager
	       /*FilterManager::class => function(ContainerInterface $c){
	     	       return new FilterManager(
	     	       	 $c->get(Aggregator::class),
		     	 	 $c->get(Translator::class),
		     	 	 $c->get(Parser::class),
		     	);
	       },*/
	       IFilterManager::class => function(ContainerInterface $c){
	     	       return new FilterManager(
	     	       	 $c->get(Aggregator::class),
		     	 	 $c->get(Translator::class),
		     	 	 $c->get(Parser::class),
		     	);
	       },

	       //inject order manager
	       IOrderManager::class => function(ContainerInterface $c){
         	       return new OrderManager();
	       },
	       OrderManager::class => function(ContainerInterface $c){
         	       return new OrderManager();
	       },

             //inject select manager
	       ISelectManager::class => function(ContainerInterface $c){
         	       return new SelectManager();
	       },
	       SelectManager::class => function(ContainerInterface $c){
         	       return new SelectManager();
	       },

	       //inject group manager
	       IGroupManager::class => function(ContainerInterface $c){
         	       return new GroupManager();
	       },
	       GroupManager::class => function(ContainerInterface $c){
         	       return new GroupManager();
	       },

	       //inject join manager
	       IJoinManager::class => function(ContainerInterface $c){
	     	       return new JoinManager();
	       },
	       JoinManager::class => function(ContainerInterface $c){
	     	       return new JoinManager();
	       },

             //inject limit manager
	       ILimitManager::class => function (ContainerInterface $c){
	     	       return new LimitManager();
	       },
	       LimitManager::class => function (ContainerInterface $c){
	     	       return new LimitManager();
	       },

	       DataFormatter::class => function(ContainerInterface $c){
	     	       return new DataFormatter();
	       },
	       
	       Security::class => function (ContainerInterface $c){
	     	       return new Security();
	       },
	       ModelManager::class => function (ContainerInterface $c){
	     	       return new ModelManager(
		     	 	 $c->get(Request::class),
		     	 	 $c->get(DbContextTracker::class)
	     	       );
	       },
	       AccountsDbContext::class => function (ContainerInterface $c){
	     	       return new AccountsDbContext($c->get(ModelManager::class));
	       },
	       ContextManager::class => function (ContainerInterface $c){
	     	       return new ContextManager();
	       },
	       Manager::class => function (ContainerInterface $c){
	     	       return new Manager($c->get(ContextManager::class));
	       },
	       MakeMigrations::class => function (ContainerInterface $c){
	     	       return new MakeMigrations($c->get(Manager::class));
	       },
	       Migrate::class => function (ContainerInterface $c){
	     	       return new Migrate($c->get(Manager::class));
	       },
	       MakeCollections::class => function (ContainerInterface $c){
	     	       return new MakeCollections($c->get(Manager::class));
	       },
	       MakeModels::class => function (ContainerInterface $c){
	     	       return new MakeModels($c->get(Manager::class));
	       },
	       MakeThroughs::class => function (ContainerInterface $c){
	     	       return new MakeThroughs($c->get(Manager::class));
	       },
	       SeedDatabase::class => function (ContainerInterface $c){
	     	       return new SeedDatabase($c->get(Manager::class));
	       },
	       MakeSuperuser::class => function (ContainerInterface $c){
	     	       return new MakeSuperuser($c->get(Manager::class));
	       },
	       StartApps::class => function (ContainerInterface $c){
	     	       return new StartApps($c->get(Manager::class));
	       },
	       StartProject::class => function (ContainerInterface $c){
	     	       return new StartProject($c->get(Manager::class));
	       },
	       ContainerService::class => DI\create(ContainerService::class)->constructor(DI\get(ContainerInterface::class)),
	 ];
?>