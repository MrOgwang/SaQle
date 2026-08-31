<?php

namespace SaQle\Modules\Platform;

use SaQle\App\App;
use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder,
	 AdminModule
};
use SaQle\Orm\Database\SystemSchema;
use SaQle\Core\Ui\Utils\Label;
use SaQle\Admin\Resources\ResourceDefinition;
use SaQle\Auth\Middleware\{
     PlatformAuthenticationMiddleware,
     PlatformAuthorizationMiddleware
};
use SaQle\Modules\Platform\Middleware\GuestOnlyMiddleware;

class Platform extends Module implements AdminModule {

	 public function contribute(ModuleBuilder $module): void {

         $schema = new SystemSchema();

         $models = $schema->get_defined_models();

	 	 $module->platform()->navigation(function($nav) use ($models) {

             $nav->groups->add(
                 name:  'resources',
                 label: 'Resources',
                 icon: ''
             );

             $nav->links->add(
                 name:  'overview',
                 label: 'Overview',
                 route: "saqle.overview",
                 icon:  'grid-2x2',
                 group: 'resources'
             );

             foreach($models as $table => $model){

                 $nav->links->add(
                     name:   strtolower($table),
                     label:  Label::make($table),
                     route:  implode(".", ["saqle", $table, "list"]),
                     icon:  'boxes',
                     group: 'resources'
                 );

             }
	 	 });

         $module->platform()->resources(function($res) use ($models){

             foreach($models as $table => $model){

                 $definition = new ResourceDefinition($model);

                 $definition->list()->component("saqle.lib.resourcelist");

                 $definition->show()->component("saqle.lib.resourceview");

                 $definition->create()->component("saqle.lib.resourcecreate");

                 $definition->edit()->component("saqle.lib.resourceedit");

                 $definition->delete()->component("saqle.lib.resourcedelete");

                 $res->add($definition);
             }

         });

         $module->routes()
         ->prefix("/saqle")
         ->name("saqle");
     }

     public function register(App $app): void {

         //register http middleware
         $app->http_middleware->add('__authentication__', PlatformAuthenticationMiddleware::class, false);
         $app->http_middleware->add('__authorization__', PlatformAuthorizationMiddleware::class, false);
         $app->http_middleware->add('__guestonly__', GuestOnlyMiddleware::class, false, false);
         
     } 
}