<?php

namespace SaQle\Console\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Console\CommandDefinition;
use SaQle\Build\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, 
    MakeThroughs, SeedDatabase, ResetDatabase, MakeSuperuser, StartProject, 
    StartApps, MakeResources, BuildProject, TestModel, RunCron, QueueCron,
    MakeComponent, MakeUser, Install, MakeEnv, MigrateStructure, RouteList
};
use SaQle\Build\Middleware\SuperUserContextMiddleware;

class FrameworkCommandsProvider extends ServiceProvider {
     public function register(): void {
         
         $superuser = new CommandDefinition(
             name: 'make:superuser',
             class: MakeSuperuser::class,
             middleware: [
                 SuperUserContextMiddleware::class
             ]
         );

         $component = new CommandDefinition(
             name: 'make:component',
             class: MakeComponent::class,
             middleware: []
         );

         $migration = new CommandDefinition(
             name: 'make:migrations',
             class: MakeMigrations::class,
             middleware: []
         );

         $migrate = new CommandDefinition(
             name: 'migrate',
             class: Migrate::class,
             middleware: []
         );

         $collections = new CommandDefinition(
             name: 'make:collections',
             class: MakeCollections::class,
             middleware: []
         );

         $models = new CommandDefinition(
             name: 'make:models',
             class: MakeModels::class,
             middleware: []
         );

         $throughs = new CommandDefinition(
             name: 'make:throughs',
             class: MakeThroughs::class,
             middleware: []
         );

         $user = new CommandDefinition(
             name: 'make:user',
             class: MakeUser::class,
             middleware: []
         );

         $seed = new CommandDefinition(
             name: 'db:seed',
             class: SeedDatabase::class,
             middleware: []
         );

         $reset = new CommandDefinition(
             name: 'db:reset',
             class: ResetDatabase::class,
             middleware: []
         );

         $build = new CommandDefinition(
             name: 'build',
             class: BuildProject::class,
             middleware: []
         );

         $install = new CommandDefinition(
             name: 'install',
             class: Install::class,
             middleware: []
         );

         $env = new CommandDefinition(
             name: 'make:env',
             class: MakeEnv::class,
             middleware: []
         );

         $routes = new CommandDefinition(
             name: 'route:list',
             class: RouteList::class,
             middleware: []
         );

         $this->app->commands->add($superuser);
         $this->app->commands->add($component);
         $this->app->commands->add($migration);
         $this->app->commands->add($migrate);
         $this->app->commands->add($collections);
         $this->app->commands->add($models);
         $this->app->commands->add($throughs);
         $this->app->commands->add($user);
         $this->app->commands->add($seed);
         $this->app->commands->add($reset);
         $this->app->commands->add($build);
         $this->app->commands->add($install);
         $this->app->commands->add($env);
         $this->app->commands->add($routes);
         
     }
}

