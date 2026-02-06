<?php

namespace SaQle\Orm\Entities\Model\Manager\Loaders;

use SaQle\Orm\Entities\Field\Types\{OneToOne, OneToMany, ManyToMany};
use SaQle\Orm\Query\Select\SelectBuilder;
use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Orm\Entities\Model\TempId;
use SaQle\Orm\Database\Db;
use SaQle\Orm\Entities\Model\Collection\ModelCollection;

final class RelationLoader {

     public function __construct(private SelectBuilder $sbuilder){}

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

     private function get_model_instance(string $connection, string $modelclass){
         $model_instance = $modelclass::make();
         $model_instance->set_table_and_connection($connection);
         return $model_instance;
     }

     private function attach_nested($nested, &$query){
         if($nested){
             $withcallbacks = $this->sbuilder->withcallbacks;
             $query->with($nested, !empty($withcallbacks) ? $withcallbacks : null);
         }
     }

     private function extract_ids(iterable $items, string $key): array {
         $ids = [];

         foreach ($items as $item){
             $item_value = $item->$key ?? null;
             if($item_value){
                 $ids[] = $item_value;
             }
         }

         return array_values(array_unique($ids));
     }

     public function load(string $connection, array | ModelCollection $parents, RelationField $relation, mixed $nested, mixed $tuning, RelationStack $relation_stack){

         $driver = Db::driver($connection);

         if($driver->supports_window_functions()){
             return $this->load_with_window_function($connection, $parents, $relation, $nested, $tuning, $relation_stack);
             //return;
         }

         /*if($tuning){
             $this->load_with_fallback($connection, $parents, $relation, $nested, $tuning, $relation_stack);
             return;
         }*/

         return $this->load_without_limit($connection, $parents, $relation, $nested, $relation_stack);
     }
     
     private function window_function_fetch($connection, $foreign_model, $foreign_key, $pkey_values, $field_name, $with, $tuning, $through, $relation_stack){
         if($through){
             $original_foreignkey = $foreign_key;
             $foreign_key = $through[3];
         }

         /**
         * Store the ids of the objects to retrieve from foreign model table in the temporary table
         * 
         * These ID values could be in hundreds, therefore using an IN clause in the resulting SQL is not sound,
         * this is why they are kept in a temporary table to be referenced later
         * */
         TempId::create($connection);
           
         if($pkey_values){
             $values_to_add = [];
             foreach($pkey_values as $id){
                 $values_to_add[] = ['id_value' => $id];
             }

             TempId::using($connection)->new($values_to_add)->save();
         }

         /**
         * Construct the sql statement that will select the id values from the temporary table above.
         * 
         * This is the statement that will be used in place of an IN clause in our final sql
         * */
         $temporary_ids_select_query = TempId::using($connection)->get()
         ->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY')
         ->select(['id_value'])->get_sql_info();

         /**
          * Fine tune how the results from the foreign model table should be by injecting:
          * 
          * Order clause  : as defined in the with callback
          * Limit clause  : as defined in the with callback
          * Filter clause : as defined in the with callback
          * Select clause : as defined in the with callback
          * */
         $order_clause    = "";
         $limit_records   = 10000;
         $raw_filters     = [];
         $selected_fields = null;

         if($tuning){ //turning is the with callback
             $tuning_manager = $foreign_model::using($connection)->get()->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
             $tuning_manager = $tuning($tuning_manager);

             $order_clause   = $tuning_manager->get_order_clause();
             $limit_records  = (int)$tuning_manager->get_limit_records();
             $limit_records  = $limit_records === 0 ? 10000 : $limit_records;
            
             $tuning_manager->l_where("row_num__lte", (int)$limit_records);

             $raw_filters     = $tuning_manager->get_raw_filters();
             $selected_fields = $tuning_manager->get_selected_fields();
         }

         $through_columns = [];
         if($through){
           if(!$selected_fields){
                 $through_model   = $through[1];
                 $through_columns = array_values($through_model::get_table_column_names());

                 //print_r($through_columns);
           }

           $throughtablename = $through[0];
                $cte_manager = $foreign_model::using($connection)->get()->config(fnqm: 'H-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
              $cte_manager->select(null, function($fields) use ($foreign_key, $order_clause){
                     return implode(", ", $fields).", ROW_NUMBER() OVER (PARTITION BY {$foreign_key}{$order_clause}) AS row_num";
               })
               ->l_where("{$foreign_key}__in", $temporary_ids_select_query['sql'])
               ->inner_join(table: $throughtablename, from: $original_foreignkey, to: $through[4]);
         }else{
           $cte_manager = $foreign_model::using($connection)->get()
               ->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY')
             ->select(null, function($fields) use ($foreign_key, $order_clause){
                    return "*, ROW_NUMBER() OVER (PARTITION BY {$foreign_key}{$order_clause}) AS row_num";
              })
              ->l_where("{$foreign_key}__in", $temporary_ids_select_query['sql']);
         }
        
         $cte_manager_query = $cte_manager->get_sql_info();

         $query_table_name = 'ranked_rows';
         $outer_manager = $foreign_model::using($connection)->get(tablealiase: $query_table_name)
         ->config(fnqm: 'N-QUALIFY', ftnm: 'A-ONLY')
         ->select($selected_fields, function($fields) use ($foreign_key, $field_name, $through_columns){
           $fields = array_merge($fields, $through_columns);
               $json_string = "";
                foreach ($fields as $_i => $f){
                   $keyparts = explode(".", $f);
                   $key = count($keyparts) === 3 ? $keyparts[2] : ( count($keyparts) === 2 ? $keyparts[1] : $keyparts[0]);
                   $json_string .= "'{$key}', {$key}";
                   if($_i < count($fields) - 1){
                    $json_string .= ", ";
                   }
                }
                $sql_string  = "{$foreign_key}, CONCAT('[', GROUP_CONCAT(JSON_OBJECT(".$json_string.") SEPARATOR ', '), ']') AS {$field_name}";
                return $sql_string;
         })
         ->set_raw_filters($raw_filters)
         ->group_by([$foreign_key]);

         $testfilters = $outer_manager->get_wbuilder()->get_where_clause($outer_manager->get_query_reference_map(), $outer_manager->get_configurations());

         $outer_manager_query = $outer_manager->get_sql_info();

         $finalsql = "WITH {$query_table_name} AS ({$cte_manager_query['sql']}) {$outer_manager_query['sql']}";

         $finalmanager = $foreign_model::using($connection)->get()->sqlndata($finalsql, $testfilters->data ? $testfilters->data : null);
         $this->attach_nested($with, $finalmanager);

         $related_data = $finalmanager->eager_load();

         //drop the temporary table
         TempId::drop();

         return $related_data;
     }

     protected function load_with_window_function(string $connection, array | ModelCollection $parents, RelationField $relation, 
        mixed $nested, mixed $tuning, RelationStack $relation_stack) {
         
         $relation_stack_parent = $relation_stack->parent();
         $local_key = $relation->get_local_key();
         $foreign_key = $relation->get_foreign_key();

         $ids = $this->extract_ids($parents, $local_key);

         if(empty($ids)) return;
         
         $assign_to_field = $relation->get_name();

         $through = null;
         if($relation instanceof ManyToMany){
             $through = [
                 $this->get_model_instance($connection, $relation->get_through())->get_table_name(), 
                 $relation->get_through(), 
                 '', 
                 $local_key, 
                 $relation->get_foreign_key()
             ];
         }
         
         $related = $this->window_function_fetch(
             $connection, 
             $relation->get_related_model(), 
             $relation->get_foreign_key(), 
             $ids, 
             $assign_to_field, 
             $nested, 
             $tuning, 
             $through,
             $relation_stack
         );

         $map = [];
         if($this->should_unpack($related, $assign_to_field)) {
             foreach($related as $row){
                 $map[$row->$local_key] = json_decode($row->$assign_to_field);
             }
         }else{
             foreach($related as $row){
                 $map[$row->$local_key][] = $row;
             }
         }

         return [
            'local_key' => $local_key,
            'mapped'  => $map,
            'assign_to' => $assign_to_field,
            'multiple' => $relation instanceof OneToOne ? false : true
         ];
     }

     protected function load_with_fallback(string $connection, array | ModelCollection $parents, RelationField $relation, 
        mixed $nested, mixed $tuning, RelationStack $relation_stack) : void {

     }

     protected function load_without_limit(string $connection, array | ModelCollection $parents, RelationField $relation,
      mixed $nested, RelationStack $relation_stack){
         $local_key = $relation->get_local_key();
         $foreign_key = $relation->get_foreign_key();
         $related_model = $relation->get_related_model();

         //Collect all foreign keys
         $ids = $this->extract_ids($parents, $local_key);

         if(empty($ids)) return;

         //Fetch all related rows at once
         if($relation instanceof ManyToMany){
             $pivot = $relation->get_through();
             $query = $pivot::using($connection)->get('t')->inner_join(
                 table: $this->get_model_instance($connection, $related_model)->get_table_name(),
                 from: $foreign_key,
                 to: $foreign_key,
                 as: 'r'
             )->where('t.'.$local_key.'__in', $ids);
         }else{
             $query = $related_model::using($connection)->get()->where($foreign_key.'__in', $ids);
         }

         $this->attach_nested($nested, $query);

         $related = $query->eager_load();

         //Index by key
         $map = [];
         if($relation instanceof ManyToMany){
             foreach($related as $row) {
                 $map[$row->$local_key][] = $row;
             }
         }else{
             foreach($related as $row) {
                 $map[$row->$foreign_key][] = $row;
             }
         }

         return [
            'local_key' => $local_key,
            'mapped'  => $map,
            'assign_to' => $relation->get_name(),
            'multiple' => $relation instanceof OneToOne ? false : true
         ];
     }
}
