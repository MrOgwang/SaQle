<?php

namespace SaQle\Modules\Admin;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder,
	 AdminModule
};
use SaQle\Core\Support\Db;
use SaQle\Core\Ui\Utils\Label;
use SaQle\Admin\Resources\ResourceDefinition;

class Admin extends Module implements AdminModule {

	 public function contribute(ModuleBuilder $module): void {

         $models = [];

         $db_schemas = Db::get_developer_schemas();

         foreach($db_schemas as $schema_name => $schema_class){
             $models = array_merge($models, new $schema_class()->get_defined_models());
         }

	 	 $module->admin()->navigation(function($nav) use ($models){

             $prefix = trim(config('admin.routes.name_prefix', "admin"));

             $nav->groups->add(
                 name:  'resources',
                 label: 'Resources',
                 icon: ''
             );

             $nav->links->add(
                 name:  'overview',
                 label: 'Overview',
                 route:  $prefix.'.overview',
                 icon:  'grid-2x2',
                 group: 'resources'
             );

             foreach($models as $table => $model){

                 $nav->links->add(
                     name:   strtolower($table),
                     label:  Label::make($table),
                     route:  implode(".", [$prefix, $table, "list"]),
                     icon:  'boxes',
                     group: 'resources'
                 );

             }

	 	 });

         $module->admin()->resources(function($res) use ($models){

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
     } 
}