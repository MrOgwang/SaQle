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
      use SaQle\Dao\Filter\Manager\FilterManager;
      use SaQle\Dao\Filter\Aggregator\Aggregator;
      use SaQle\Dao\Filter\Translator\Translator;
      use SaQle\Dao\Filter\Parser\Parser;
      use SaQle\Dao\Order\Manager\OrderManager;
      use SaQle\Dao\Connection\Connection;
      use SaQle\Dao\Formatter\DataFormatter;
      use SaQle\Dao\Join\Manager\JoinManager;
      use SaQle\Dao\Limit\Manager\LimitManager;
      use SaQle\Dao\DbContext\Trackers\DbContextTracker;
      use SaQle\Dao\Select\Manager\SelectManager;
      use SaQle\Security\Security;
      use SaQle\Http\Request\Request;
      use SaQle\Services\Container\ContainerService;
      use SaQle\Migration\Managers\{ContextManager, Manager};
      use SaQle\Migration\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, MakeThroughs, SeedDatabase, MakeSuperuser};

	 return [
             Request::class => function(ContainerInterface $c){
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
	       FilterManager::class => function(ContainerInterface $c){
	     	       return new FilterManager(
	     	       	 $c->get(Aggregator::class),
		     	 	 $c->get(Translator::class),
		     	 	 $c->get(Parser::class),
		     	);
	       },
	       OrderManager::class => function(ContainerInterface $c){
         	       return new OrderManager();
	       },
	       SelectManager::class => function(ContainerInterface $c){
         	       return new SelectManager();
	       },
	       DataFormatter::class => function(ContainerInterface $c){
	     	       return new DataFormatter();
	       },
	       JoinManager::class => function(ContainerInterface $c){
	     	       return new JoinManager();
	       },
	       LimitManager::class => function (ContainerInterface $c){
	     	       return new LimitManager();
	       },
	       Security::class => function (ContainerInterface $c){
	     	       return new Security();
	       },
	       ModelManager::class => function (ContainerInterface $c){
	     	       return new ModelManager(
		     	 	 $c->get(Request::class),
		     	 	 $c->get(FilterManager::class),
		     	 	 $c->get(DbContextTracker::class),
		     	 	 $c->get(JoinManager::class),
		     	 	 $c->get(LimitManager::class),
		     	 	 $c->get(OrderManager::class),
		     	 	 $c->get(SelectManager::class),
		     	 	 $c->get(DataFormatter::class ),
		     	 	 $c->get(Connection::class),
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
	       ContainerService::class => DI\create(ContainerService::class)->constructor(DI\get(ContainerInterface::class)),
	 ];
?>