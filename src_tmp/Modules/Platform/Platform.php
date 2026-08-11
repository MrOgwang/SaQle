<?php

namespace SaQle\Modules\Platform;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder,
	 AdminModule
};
use SaQle\Orm\Database\SystemSchema;
use SaQle\Core\Ui\Utils\Label;
use SaQle\Admin\Resources\ResourceDefinition;

class Platform extends Module implements AdminModule {

	 public function contribute(ModuleBuilder $module): void {

         $schema = new SystemSchema();

         $models = $schema->get_defined_models();

	 	 $module->admin()->navigation(function($nav) use ($models) {

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