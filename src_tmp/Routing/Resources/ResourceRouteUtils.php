<?php
namespace SaQle\Routing\Resources;

use SaQle\Orm\Database\SystemSchema;
use SaQle\Core\Support\Db;
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Auth\Context\ActorContext;
use SaQle\Core\Ui\Forms\{
     FormMode,
     FormModelResolver
};

trait ResourceRouteUtils {

     protected ?string $tenant_slug = null;
     protected bool    $multitenancy = false;

     public function __construct(){
         $this->tenant_slug = request()->tenant?->slug;
         $this->multitenancy = (bool)config('tenancy.enabled');
     }
 
     private function table_name_to_label(string $name) : string {
         $name = str_replace(['_', '-'], ' ', $name);
         return ucwords($name);
     }

     protected function list_route_def(
         string $model_label, 
         string $model_class,
         bool   $is_platform
     ){
         return (Object)[
             'url' => admin_route_url($model_label, [], $is_platform),
             'ui_label' => $this->table_name_to_label($model_label),
             'plural_label' => $model_label,
             'singular_label' => ModelRegistry::get_model_name($model_class),
             'route_name' => admin_route_name($model_label, "list", $is_platform),
             'pk_column' => $model_class::get_pk_name()
         ];
     }

     protected function get_resource_links(){

         $links = [];

         if(ActorContext::is_platform()){
             $system_schema = new SystemSchema();
             $system_models = $system_schema->get_defined_models();

             foreach($system_models as $model_label => $model_class){
                 $links[$model_class] = $this->list_route_def($model_label, $model_class, true);
             }
         }else{
             //get developer defined db schemas
             $db_schemas = Db::get_developer_schemas();

             foreach($db_schemas as $schema_name => $schema_class){
                 $models = new $schema_class()->get_defined_models();

                 foreach($models as $model_label => $model_class){
                     $links[$model_class] = $this->list_route_def($model_label, $model_class, false);
                 }
             }
         }

         return $links;
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
