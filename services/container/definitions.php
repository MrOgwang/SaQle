<?php
      /**
       * Dependency injection management definitions
       * */
      use Psr\Container\ContainerInterface;
      use function DI\factory;
      use function DI\create;
      use SaQle\Orm\Entities\Model\Manager\ModelManager;
      use SaQle\Orm\Database\DbTypes;
      use SaQle\Orm\Database\DbPorts;
      use SaQle\Orm\Database\Attributes\DbContextOptions;
      use SaQle\Orm\Database\Trackers\DbContextTracker;
      
      use SaQle\Orm\Query\Where\Aggregator;
      use SaQle\Orm\Query\Where\Translator;
      use SaQle\Orm\Query\Where\Parser;
      use SaQle\Orm\Query\Where\WhereBuilder;
      use SaQle\Orm\Query\Order\OrderBuilder;
      use SaQle\Orm\Connection\Connection;
      use SaQle\Orm\Entities\Field\Formatter\DataFormatter;
      use SaQle\Orm\Query\Join\JoinBuilder;
      use SaQle\Orm\Query\Limit\LimitBuilder;
      use SaQle\Orm\Query\Select\SelectBuilder;
      use SaQle\Orm\Query\Group\GroupBuilder;

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
	       WhereBuilder::class => function(ContainerInterface $c){
	     	       return new WhereBuilder(
	     	       	 $c->get(Aggregator::class),
		     	 	 $c->get(Translator::class),
		     	 	 $c->get(Parser::class),
		     	);
	       },
	       OrderBuilder::class => function(ContainerInterface $c){
         	       return new OrderBuilder();
	       },
	       SelectBuilder::class => function(ContainerInterface $c){
         	       return new SelectBuilder();
	       },
	       GroupBuilder::class => function(ContainerInterface $c){
         	       return new GroupBuilder();
	       },
	       JoinBuilder::class => function(ContainerInterface $c){
	     	       return new JoinBuilder();
	       },
	       LimitBuilder::class => function (ContainerInterface $c){
	     	       return new LimitBuilder();
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