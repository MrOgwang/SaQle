<?php

namespace SaQle\Core\Files\Storage;

use SaQle\Core\Files\{TempFileRef, UploadedFile};
use RuntimeException;

final class TempStorage {

     private static function base(): string {
         return config('base_path') . '/storage/tmp/uploads';
     }

     public static function store(string $model, string $field, string $session, UploadedFile $file): TempFileRef {
         $file_id = bin2hex(random_bytes(8));
         $bucket  = substr($file_id, 0, 2);
         $ext     = strtolower($file->extension());

         $dir = self::path($model, $field, $session, $bucket);
         self::ensure_dir($dir);

         $target = $dir.'/'.$file_id.'.'.$ext;

         if(!move_uploaded_file($file->tmp_name, $target)){
             throw new RuntimeException("Failed to move uploaded file");
         }

         chmod($target, 0600);

         self::write_meta($model, $field, $session, [
            'id'            => $file_id,
            'original_name' => $file->name,
            'mime'          => $file->type,
            'size'          => $file->size,
            'stored_at'     => time()
         ]);

         return new TempFileRef($model, $field, $session, $file_id);
     }

     public static function resolve(TempFileRef $ref): string {
         $glob = glob(self::base()."/{$ref->model}/{$ref->field}/{$ref->session}/*/{$ref->file_id}.*");

         if(!$glob){
             throw new RuntimeException("Temp file not found");
         }

         return $glob[0];
     }

     private static function path(...$parts): string {
         return self::base().'/'.implode('/', $parts);
     }

     private static function ensure_dir(string $dir): void {
         if(!is_dir($dir)){
             mkdir($dir, 0750, true);
         }
     }

     private static function write_meta(string $model, string $field, string $session, array $file_meta): void {
         $meta_path = self::base()."/{$model}/{$field}/{$session}/meta.json";

         $meta = is_file($meta_path) ? 
         json_decode(file_get_contents($meta_path), true) : 
         [
             'model' => $model, 
             'field' => $field, 
             'session' => $session, 
             'created_at' => time(), 
             'expires_at' => time() + 3600,
             'files' => []
         ];

         $meta['files'][] = $file_meta;

         file_put_contents($meta_path, json_encode($meta, JSON_PRETTY_PRINT), LOCK_EX);
     }
}
