<?php

namespace SaQle\Core\Files\Commits;

final class FileCommitCoordinator {
     protected array $committed = [];

     public function commit(object $model, array $files, array $row): void {
         foreach($files['references'] ?? [] as $field_name => $refs){

             $field = $model->meta->fields[$field_name];

             if(!method_exists($field, 'get_committer')){
                 continue;
             }

             $committer = $field->get_committer();

             $refs = is_array($refs) ? $refs : [$refs];

             $paths = $committer->commit($model, $refs, $row);

             $this->committed[] = ['committer' => $committer, 'paths' => $paths];
         }
     }

     public function rollback(): void {
         foreach(array_reverse($this->committed) as $entry){
             $entry['committer']->rollback($entry['paths']);
         }
     }
}