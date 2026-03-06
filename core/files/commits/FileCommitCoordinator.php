<?php

namespace SaQle\Core\Files\Commits;

use SaQle\Core\Files\Storage\TempStorage;

final class FileCommitCoordinator {
     protected array $committed = [];

     public function commit(object $model, array $files, string $session, mixed $row): void {

         foreach($files as $field_name => $refs){

             $field = $model->table->get_clean_fields()[$field_name];

             $multiple = $field->get_multiple();

             if(!method_exists($field, 'get_committer')){
                 continue;
             }

             $committer = $field->get_committer();

             $refs = is_array($refs) ? $refs : [$refs];
             
             $commits = $committer->commit($model, $refs, $row);

             $this->committed[$field_name] = [
                 'committer' => $committer, 
                 'commits' => $multiple ? $commits : $commits[0]
             ];
         }

         //Cleanup session directory
         TempStorage::cleanup_session($session);
     }

     public function rollback(): void {
         foreach(array_reverse($this->committed) as $entry){
             $entry['committer']->rollback($entry['commits']);
         }
     }

     public function get_comitted_files(){
         $committed_files = [];

         foreach($this->committed as $fn => $entry){
             $committed_files[$fn] = json_encode($entry['commits']);
         }

         return $committed_files;
     }
}