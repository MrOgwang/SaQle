<?php
/**
 * DataMiddleware
 *
 * Collects all input sources (GET, POST, FILES, SESSION, and raw bodies from PUT/PATCH/DELETE)
 * and merges them into $request->data for uniform access.
 *
 * Example:
 *   $name = $request->data->get('name', 'default');
 *
 */
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

class DataMiddleware extends IMiddleware {
     public function handle(MiddlewareRequestInterface &$request){
         $data = [];

         //1. Query parameters (GET)
         if (!empty($_GET)) {
             $data = array_merge($data, $_GET);
         }

         //2. Form submissions (POST) | - x-www-form-urlencoded | - multipart/form-data
         if(!empty($_POST)) {
             $data = array_merge($data, $_POST);
         }

         //3. Raw body (JSON / form-encoded) | Applies to POST, PUT, PATCH, DELETE
         $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

         if(in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])){
             $raw = file_get_contents('php://input');

             if($raw !== false && trim($raw) !== ''){
                 $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                 //JSON payload
                 if(str_contains($contentType, 'application/json')){
                     $json = json_decode($raw, true);

                     if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                         $data = array_merge($data, $json);
                     }
                 }
                 // Form-encoded fallback
                 else {
                     $parsed = [];
                     parse_str($raw, $parsed);

                     if (!empty($parsed)) {
                         $data = array_merge($data, $parsed);
                     }
                 }
             }
         }

         //4. Files (normalized)
         if (!empty($_FILES)) {
             $data = array_merge($data, $this->normalize_files($_FILES));
         }

         //5. Inject into request data bag
         foreach ($data as $key => $value) {
             $request->data->set($key, $value);
         }

         parent::handle($request);
     }

     //Normalize $_FILES into a predictable structure
     private function normalize_files(array $files): array {
        $normalized = [];

         foreach ($files as $field => $info) {

            // Multiple files
            if (is_array($info['name'])) {
                $normalized[$field] = [];

                foreach ($info['name'] as $i => $name) {
                    if ($info['error'][$i] === UPLOAD_ERR_OK) {
                        $normalized[$field][] = [
                            'name'      => $name,
                            'full_path'=> $info['full_path'][$i] ?? null,
                            'type'      => $info['type'][$i],
                            'tmp_name'  => $info['tmp_name'][$i],
                            'error'     => $info['error'][$i],
                            'size'      => $info['size'][$i],
                        ];
                    }
                }
            }
            // Single file
            else {
                if ($info['error'] === UPLOAD_ERR_OK) {
                    $normalized[$field] = [
                        'name'      => $info['name'],
                        'full_path'=> $info['full_path'] ?? null,
                        'type'      => $info['type'],
                        'tmp_name'  => $info['tmp_name'],
                        'error'     => $info['error'],
                        'size'      => $info['size'],
                    ];
                } else {
                    $normalized[$field] = null;
                }
            }
         }

         return $normalized;
     }
}

