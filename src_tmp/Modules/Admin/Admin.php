<?php

namespace SaQle\Modules\Admin;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder,
	 AdminModule
};
use SaQle\Routing\Resources\ResourceRouteUtils;
use SaQle\Auth\Context\ActorContext;
use SaQle\Admin\Resources\ResourceDefinition;

class Admin extends Module implements AdminModule {

     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(){
         $this->__utilsConstruct();
     }

	 public function contribute(ModuleBuilder $module): void {

         $resources = $this->get_resource_links();

	 	 $module->admin()->navigation(function($nav) use ($resources) {

             $is_platform = ActorContext::is_platform();

             $nav->groups->add(
                 name:  'resources',
                 label: 'Resources',
                 icon: ''
             );

             $nav->links->add(
                 name:  'overview',
                 label: 'Overview',
                 route:  $is_platform ? "saqle.overview" : config('admin.routes.name_prefix', "admin").'.overview',
                 icon:  'grid-2x2',
                 group: 'resources'
             );

             foreach($resources as $r){
                 $nav->links->add(
                     name:  strtolower($r->ui_label),
                     label: $r->ui_label,
                     route:  admin_route_name($r->plural_label, 'list', $is_platform),
                     icon:  'boxes',
                     group: 'resources'
                 );
             }

	 	 });

	 	 /*$module->admin()->resources(function($res) use ($resources) {

             foreach($resources as $model => $resource){
 
                 $definition = new ResourceDefinition($model);

                 $definition->list()->component("saqle.lib.resourcelist");

                 $definition->show()->component("saqle.lib.resourceview");

                 $definition->create()->component("saqle.lib.resourcecreate");

                 $definition->edit()->component("saqle.lib.resourceedit");

                 $definition->delete()->component("saqle.lib.resourcedelete");

                 $res->add($definition);

             }

	 	 });*/

     }

}