<?php
namespace SaQle\Routes\Resources;

use SaQle\Orm\Database\SystemSchema;
use SaQle\Core\Support\Db;
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Auth\Context\ActorContext;

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

     private function construct_url($type, $model_label){
         return $type === 'system' ? 
         '/saqle/_admin/'.$model_label : 
         ( $this->multitenancy ? 
            '/'.$this->tenant_slug.'/_admin/'.$model_label : 
            '/_admin/'.$model_label
         );
     }

     protected function list_route_def(
         string $type, 
         string $model_label, 
         string $model_class
     ){
         return (Object)[
             'url' => $this->construct_url($type, $model_label),
             'ui_label' => $this->table_name_to_label($model_label),
             'plural_label' => $model_label,
             'singular_label' => ModelRegistry::get_model_name($model_class),
             'route_name' => rr_name($model_label, 'list'),
             'pk_column' => $model_class::get_pk_name()
         ];
     }

     protected function get_resource_links(){
         $links = [];

         if(ActorContext::is_platform()){
             $system_schema = new SystemSchema();
             $system_models = $system_schema->get_admin_models();

             foreach($system_models as $model_label => $model_class){
                 $links[$model_class] = $this->list_route_def('system', $model_label, $model_class);
             }
         }else{
             //get developer defined db schemas
             $db_schemas = Db::get_developer_schemas();

             foreach($db_schemas as $schema_name => $schema_class){
                 $models = new $schema_class()->get_admin_models();

                 foreach($models as $model_label => $model_class){
                     $links[$model_class] = $this->list_route_def('tenant', $model_label, $model_class);
                 }
             }
         }

         return $links;
     }
}
