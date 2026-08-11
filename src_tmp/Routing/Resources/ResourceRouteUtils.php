<?php

namespace SaQle\Routing\Resources;

use SaQle\Core\Registries\{
     ModelRegistry,
     TableRegistry
};
use SaQle\Auth\Context\ActorContext;
use SaQle\Core\Ui\Forms\{
     FormMode,
     FormModelResolver
};
use SaQle\Core\Ui\Utils\Label;

trait ResourceRouteUtils {

     protected function resource(string $model){

         $prefix = ActorContext::is_platform() ? 'saqle' : trim(config('admin.routes.name_prefix', "admin"));

         $table = TableRegistry::get_model_table($model);

         return (Object)[
             'model'             => $model,
             'table'             => $table,
             'plural_label'      => Label::make($table),
             'singular_label'    => ModelRegistry::get_model_name($model),
             'create_route'      => implode(".", [$prefix, $table, "create"]),
             'edit_route'        => implode(".", [$prefix, $table, "edit"]),
             'list_route'        => implode(".", [$prefix, $table, "list"]),
             'create_form_route' => implode(".", [$prefix, $table, "create.form"]),
             'edit_form_route'   => implode(".", [$prefix, $table, "edit.form"]),
             'del_route'         => implode(".", [$prefix, $table, "delete"]),
             'show_route'        => implode(".", [$prefix, $table, "show"]),
             'pk'                => $model::get_pk_name()
         ];
     }

     private function create_auto_form(FormMode $mode, array $props = []){

         if(array_key_exists('name', $props)){
             [, $model_class, $form_name] = FormModelResolver::resolve($props['name']);
         }else{
             $model_class = request()->route->model_class;
             $form_name = match($mode){
                 FormMode::CREATE => 'default_create',
                 FormMode::UPDATE => 'default_update'
             };
         }

         $form_def = $model_class::get_forms_definition();

         return $form_def->forms[$form_name] ?? null;
     }
}
