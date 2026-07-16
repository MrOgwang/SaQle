<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Details;

use SaQle\Core\Ui\Utils\{
     Panel,
     Label
};
use SaQle\Routes\Resources\ResourceRouteUtils;
use SaQle\Routes\UrlBuilder;

final class DetailView {

     use Panel {
         Panel::__construct as private __panelConstruct;
     }
     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public private(set) array $meta = [] {
         set(array $value){
             $this->meta = $value;
         }

         get => $this->meta;
     }

     public private(set) array $general = [] {
         set(array $value){
             $this->general = $value;
         }

         get => $this->general;
     }

     public private(set) array $relations = [] {
         set(array $value){
             $this->relations = $value;
         }

         get => $this->relations;
     }

     public private(set) array $descriptions = [] {
         set(array $value){
             $this->descriptions = $value;
         }

         get => $this->descriptions;
     }

     public function __construct(string $model, array $props = []){
         $this->__panelConstruct($model, $props, 'detail');
         $this->__utilsConstruct();
         $this->fetch_model_data();
         $this->form_data_groups();
         $this->fetch_relations();
     }

     private function form_data_groups(){ 

         $general = [];
         $meta = [];
         $descriptions = [];

         foreach($this->fields as $name => $field){
             if(isset($this->columns[$name])){
                 if($this->columns[$name]->type === 'general'){
                     $general[$field->label] = $this->data->$name;
                 }elseif($this->columns[$name]->type === 'meta'){
                     $meta[$field->label] = $this->data->$name;
                 }elseif($this->columns[$name]->type === 'description'){
                     $descriptions[$field->label] = $this->data->$name;
                 }
             }
         }

         $this->general = $general;
         $this->meta = $meta;
         $this->descriptions = $descriptions;
     }

     protected function fetch_relations(){

         $model_fields = $this->model::get_fields();
         $nav_field_names   = $this->model::get_nav_field_names();

         $relations = [];

         $resources = $this->get_resource_links();

         foreach($nav_field_names as $name){

             $field = $model_fields[$name];

             $local_key = $field->get_local_key();
             $foreign_key = $field->get_foreign_key();
             $related_model = $field->get_related_model();

             $local_key_value = $this->data->{$local_key};

             $count = $related_model::get()->where($foreign_key."__eq", $local_key_value)->count();

             $current_resource = $resources[$related_model] ?? null;

             $url = "#";
             if($current_resource){
                 $url = new UrlBuilder($current_resource->url)
                 ->filter($foreign_key, $local_key_value);
             }

             $relations[$name] = (Object)[
                 'label' => Label::make($name),
                 'count' => $count,
                 'url'   => $url
             ];
         }

         $this->relations = $relations;
         
     }

     protected function fetch_model_data(){

         $query = $this->model::get();

         if($this->fk_columns){
             $query->with($this->fk_columns);
         }

         $this->data = $query->where($this->model::get_pk_name()."__eq", $this->props['id'])->first_or_fail();
     }
}