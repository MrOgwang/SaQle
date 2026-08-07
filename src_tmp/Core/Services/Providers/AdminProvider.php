<?php

namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Admin\Admin;
use SaQle\Routes\Resources\ResourceRouteUtils;
use SaQle\App\App;
use SaQle\Auth\Context\ActorContext;
use SaQle\Admin\Resources\ResourceDefinition;

class AdminProvider extends ServiceProvider {

     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(App $app){
         parent::__construct($app);
         $this->__utilsConstruct();
     }

     public function register(): void {

         $resources = $this->get_resource_links();

         Admin::navigation(function($nav) use ($resources){

             $nav->groups->add(
                 name:  'resources',
                 label: 'Resources',
                 icon: ''
             );

             $nav->links->add(
                 name:  'overview',
                 label: 'Overview',
                 url:   ActorContext::is_platform() ? 
                       '/saqle/resources/overview' : '/'.config('admin.routes.prefix', "_admin").'/resources/overview/',
                 icon:  'grid-2x2',
                 group: 'resources'
             );

             foreach($resources as $r){
                 $nav->links->add(
                     name:  strtolower($r->ui_label),
                     label: $r->ui_label,
                     url:   $r->url,
                     icon:  'boxes',
                     group: 'resources'
                 );
             }

         });
         
         Admin::resources(function($res) use ($resources){

             foreach($resources as $model => $resource){
 
                 $definition = new ResourceDefinition($model);

                 $definition->list()->component("saqle.resourcelist");

                 $definition->show()->component("saqle.resourceview");

                 $definition->create()->component("saqle.resourcecreate");

                 $definition->edit()->component("saqle.resourceedit");

                 $definition->delete()->component("saqle.resourcedelete");

                 $res->add($definition);

             }

         });

     }
}

