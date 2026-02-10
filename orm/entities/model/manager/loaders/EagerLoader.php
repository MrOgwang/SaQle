<?php

namespace SaQle\Orm\Entities\Model\Manager\Loaders;

use SaQle\Orm\Query\Select\SelectBuilder;
use SaQle\Orm\Entities\Model\Collection\{GenericModelCollection, ModelCollection};

class EagerLoader {

     private function should_unpack(array | ModelCollection $parents, ?string $pointerkey = null){
         if(!$parents){
             return false;
         }

         if($parents instanceof ModelCollection){
             $item_keys = array_keys($parents[0]->get_data());
         }else{
             $item_keys = array_keys(get_object_vars($parents[0]));
         }

         if(in_array($pointerkey, $item_keys)){
             return true;
         }

         return false;
     }

     public function load(string $connection, array | ModelCollection $parents, array $relations, array $nested_includes, 
        array $includes_tuning, SelectBuilder $sbuilder, RelationStack $relation_stack){
        
         $loader = new RelationLoader($sbuilder);
         foreach($relations as $index => $relation){
             $model_class = $relation->get_related_model();
             $model_collection_class = $model_class::collection_class();

             //record this in the relation stack
             $relation_stack->enter($relation->get_name());

             //unpack parents
             $relation_stack_parent = $relation_stack->parent();

             if($relation_stack_parent && $this->should_unpack($parents, $relation_stack_parent)){
                 $unpacked = [];
                 foreach($parents as $r){
                     $unpacked = array_merge($unpacked, json_decode($r->$relation_stack_parent));
                 }
                 $parents = $unpacked;
             }

             //get cascading nesting and query tuning callbacks
             $nested = $nested_includes[$index] ?? null;
             $tuning = $includes_tuning[$index] ?? null;

             //load includes
             $include_data = $loader->load($connection, $parents, $relation, $nested, $tuning, $relation_stack);

             //atach to parents
             foreach($parents as $p){
                 $assign_to = $include_data['assign_to'];
                 $local_key = $include_data['local_key'];
                 $local_key_value = $p->$local_key;
                 if($include_data['multiple']){
                     //$p->$assign_to = $include_data['mapped'][$local_key_value] ?? [];
                     if($model_collection_class == GenericModelCollection::class){
                         $p->$assign_to = $model_collection_class::from_objects($model_class, $include_data['mapped'][$local_key_value] ?? []);
                     }else{
                         $p->$assign_to = new $model_collection_class($include_data['mapped'][$local_key_value] ?? []);
                     }
                 }else{
                     $p->$assign_to = new $model_class(
                         ...get_object_vars($include_data['mapped'][$local_key_value][0])
                     ) ?? null;

                     //$p->$assign_to = $include_data['mapped'][$local_key_value][0] ?? null;
                 }
             }
            
             //remove this from relation stack
             $relation_stack->leave();
         }

         return $parents;
     }
}