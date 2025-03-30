<?php
namespace SaQle\Services;

use SaQle\Core\Services\Container\ServiceLocator;
use SaQle\Core\Services\Container\Container;
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
use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Log\FileLogger;
use SaQle\Auth\Models\Interfaces\SessionUser;

class DefaultServiceLocator extends ServiceLocator {
     public function register(Container $container): void {
         $container->singleton(Request::class, function($c){
             return Request::init();
         });
         $container->singleton('request', function($c){
             return Request::init();
         });
         $container->bind(DbContextTracker::class);
         $container->bind(Parser::class);
         $container->bind(Translator::class);
         $container->bind(Aggregator::class);
         $container->bind(WhereBuilder::class);
         $container->bind(OrderBuilder::class);
         $container->bind(SelectBuilder::class);
         $container->bind(GroupBuilder::class);
         $container->bind(JoinBuilder::class);
         $container->bind(LimitBuilder::class);
         $container->bind(DataFormatter::class);
         $container->bind(Security::class);
         $container->bind(ContextManager::class);
         $container->bind(IMigrationManager::class, ContextManager::class);
         $container->bind(Manager::class);
         $container->bind(MakeMigrations::class);
         $container->bind(Migrate::class);
         $container->bind(MakeCollections::class);
         $container->bind(MakeModels::class);
         $container->bind(MakeThroughs::class);
         $container->bind(SeedDatabase::class);
         $container->bind(MakeSuperuser::class);
         $container->bind(StartApps::class);
         $container->bind(StartProject::class);
         $container->bind(DbContextOptions::class, function($c, $name, $type, $port, $username, $password){
             return new DbContextOptions(name: $name, type: $type, port: $port, username: $username, password: $password);
         });
         $container->bind(Connection::class, function($c, $name, $type, $port, $username, $password){
             return Connection::make($c->resolve(DbContextOptions::class,  [$name, $type, $port, $username, $password]));
         });
         $container->bind(FileLogger::class, function($c, $path, $mode){
             return new FileLogger(file_path: $path, file_mode: $mode);
         });
         $container->bind(SessionUser::class, function($c){
             $request = $c->resolve('request');
             return $request->user;
         });
     }
}
?>
