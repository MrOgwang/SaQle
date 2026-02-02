<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Orm\Database\DbContextOptions;
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
use SaQle\Build\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, MakeThroughs, SeedDatabase, MakeSuperuser, StartApps, StartProject, MakeResources};
use SaQle\Log\FileLogger;
use SaQle\Auth\Models\Interfaces\SessionUser;
use SaQle\Auth\Services\AuthService;
use SaQle\Routes\Router;
use SaQle\Core\Registries\{EventRegistry, RouteRegistry, CachedEventRegistry};
use SaQle\Core\Events\EventBus;
use SaQle\Routes\Canonical\CanonicalUrlPolicy;
use SaQle\Routes\Canonical\TrailingSlashPolicy;

class FrameworkDIProvider extends ServiceProvider {
     public function register(): void {
         $this->app->container->singleton(Request::class, function($c){
             return Request::init();
         });
         $this->app->container->singleton('request', function($c){
             return Request::init();
         });
         $this->app->container->bind(DbContextTracker::class);
         $this->app->container->bind(Parser::class);
         $this->app->container->bind(Translator::class);
         $this->app->container->bind(Aggregator::class);
         $this->app->container->bind(WhereBuilder::class);
         $this->app->container->bind(OrderBuilder::class);
         $this->app->container->bind(SelectBuilder::class);
         $this->app->container->bind(GroupBuilder::class);
         $this->app->container->bind(JoinBuilder::class);
         $this->app->container->bind(LimitBuilder::class);
         $this->app->container->bind(DataFormatter::class);
         $this->app->container->bind(Security::class);
         $this->app->container->bind(MakeMigrations::class);
         $this->app->container->bind(Migrate::class);
         $this->app->container->bind(MakeCollections::class);
         $this->app->container->bind(MakeModels::class);
         $this->app->container->bind(MakeThroughs::class);
         $this->app->container->bind(SeedDatabase::class);
         $this->app->container->bind(MakeSuperuser::class);
         $this->app->container->bind(StartApps::class);
         $this->app->container->bind(MakeResources::class);
         $this->app->container->bind(StartProject::class);
         $this->app->container->bind(DbContextOptions::class, function($c, ...$connection_params){
             return new DbContextOptions(...$connection_params);
         });
         $this->app->container->bind(Connection::class, function($c, ...$connection_params){
             return Connection::make($c->resolve(DbContextOptions::class, $connection_params));
         });
         $this->app->container->bind(FileLogger::class, function($c, $path, $mode){
             return new FileLogger(file_path: $path, file_mode: $mode);
         });
         $this->app->container->bind(SessionUser::class, function($c){
             $request = $c->resolve('request');
             return $request->user;
         });
         $this->app->container->bind(AuthService::class);
         $this->app->container->bind(Router::class);
         $this->app->container->bind(RouteRegistry::class);

         $this->app->container->bind(EventRegistry::class, fn () => $this->app->events);

         $this->app->container->bind(EventBus::class, fn () =>
             new EventBus($this->app->container->resolve(EventRegistry::class))
         );
         $this->app->container->bind(CanonicalUrlPolicy::class, function(){
             return new TrailingSlashPolicy();
         });
     }
}

