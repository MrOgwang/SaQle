<?php

namespace SaQle\Core\Files\Commits;

interface FileCommitInterface {
     public function commit(object $model, array $temp_refs, array $created_row) : array;

     public function rollback(array $committed_paths) : void;
}