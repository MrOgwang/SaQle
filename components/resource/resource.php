<?php
declare(strict_types=1);

namespace SaQle\Components\Resource;

use SaQle\Routes\MatchedRoute;
use SaQle\Orm\Entities\Model\Schema\Model;
use RuntimeException;
use SaQle\Http\Request\Request;

final class Resource {
     //Handle GET collection or single resource
     public function get(Request $request){
        $model_class = $this->resolve_model($request);

        /** @var Model $query */
        $query = $model_class::get();

        // Apply filters from query string
        $query = $this->apply_filters($query, $request->queries);

        // Apply sorting
        $query = $this->apply_sorting($query, $request->queries);

        // Apply pagination
        $query = $this->apply_pagination($query, $request->queries);

        // Handle relationships
        if ($rel = $this->relationship_request($request)) {
            return $this->fetch_relationship($query, $request->params['id'], $rel);
        }

        // If :id is present, fetch single resource
        if (!empty($request->params['id'])) {
            $record = $query->where('id', $request->params['id'])->first();
            if (!$record) {
                return ['error' => 'Resource not found', 'status' => 404];
            }
            return $record;
        }

        // Return collection
        return $query->all();
     }

     //Handle POST / bulk create
     public function post(Request $request){
         $model_class = $this->resolve_model($request);
         $fillable = $model_class::get_fillable_fields();

         $insert_data = $request->data->get_many(
             keys: array_keys($fillable), 
             all_must_exist: false, 
             defaults: array_values($fillable), 
             keep_keys: true
         );

         $object = $model_class::new($insert_data)->save();

         return $object;
     }

     //Handle PATCH / bulk update
     public function patch(Request $request){
         $model_class = $this->resolve_model($request);

         $data = $request->data;

         if (!empty($request->params['id'])) {
            $record = $model_class::get()->where('id', $request->params['id'])->first();
            if (!$record) {
                return ['error' => 'Resource not found', 'status' => 404];
            }
            $record->update($data);
            return $record;
         }

         // Bulk update: must have filter in query
         if (empty($request->queries)) {
            throw new RuntimeException("Query parameters required for bulk update");
         }

         $query = $this->apply_filters($model_class::get(), $request->queries);
         $records = $query->all();

         foreach ($records as $record) {
            $record->update($data);
         }

         return $records;
     }

     //Handle DELETE / bulk delete or single
     public function delete(object $request){
         $model_class = $this->resolve_model($request);

         if (!empty($request->params['id'])) {
             $record = $model_class::get()->where('id', $request->params['id'])->first();
             if (!$record) {
                return ['error' => 'Resource not found', 'status' => 404];
             }
             $record->delete();
             return ['status' => 'deleted'];
         }

        // Bulk delete: must have filter in query
        if (empty($request->queries)) {
            throw new RuntimeException("Query parameters required for bulk delete");
        }

        $query = $this->apply_filters($model_class::get(), $request->queries);
        $records = $query->all();

        foreach ($records as $record) {
            $record->delete();
        }

        return ['deleted_count' => count($records)];
     }

     /* ================== Helpers ================== */

     private function resolve_model(Request $request): string{
         if (!isset($request->route->model_class)){
             throw new RuntimeException("Resource route missing model_class");
         }

         return $request->route->model_class;
     }

     private function apply_filters($query, array $filters){
         foreach ($filters as $field_op => $value){
            //Allow operators like age__gt, name__like
             if (str_contains($field_op, '__')) {
                 [$field, $op] = explode('__', $field_op, 2);
                 $query->where_operator($field, $op, $value);
             }else{
                 $query->where($field_op, $value);
             }
         }
         return $query;
     }

     private function apply_sorting($query, array $queries){
         if (isset($queries['sort'])) {
            // sort=field1,-field2
            $fields = explode(',', $queries['sort']);
            foreach ($fields as $f) {
                $direction = str_starts_with($f, '-') ? 'desc' : 'asc';
                $field_name = ltrim($f, '-');
                $query->order_by($field_name, $direction);
            }
         }
         return $query;
     }

     private function apply_pagination($query, array $queries){
         $page = max(1, (int)($queries['page'] ?? 1));
         $limit = max(1, (int)($queries['limit'] ?? 25));
         $offset = ($page - 1) * $limit;
         return $query->limit($limit)->offset($offset);
     }

     private function relationship_request(object $request): ?string{
         return $request->params['rel'] ?? null;
     }

     private function fetch_relationship($query, $id, string $relationship){
         $record = $query->where('id', $id)->first();
         if (!$record) {
             return ['error' => 'Resource not found', 'status' => 404];
         }

         if (!method_exists($record, $relationship)) {
             return ['error' => 'Relationship not found', 'status' => 404];
         }

         return $record->$relationship()->all();
     }
}
