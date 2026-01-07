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

class DataMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         $data = [];

         //merge get and post variables
         $data = array_merge($data, $_GET, $_POST);

         //merge session variables
         /*if(session_status() === PHP_SESSION_ACTIVE) {
             $data = array_merge($data, $_SESSION);
         }*/

         //merge files
         if(!empty($_FILES)){
             $data = array_merge($data, $this->normalize_files($_FILES));
         }

         $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

         //merge form submissions (application/x-www-form-urlencoded or multipart/form-data)
         if(in_array($method, ['PUT', 'PATCH', 'DELETE'])){
             $raw_input = file_get_contents('php://input');

             //try JSON first
             $json = json_decode($raw_input, true);

             if(json_last_error() === JSON_ERROR_NONE && is_array($json)){
                 $data = array_merge($data, $json);
             }else{
                 //Fallback: assume form-encoded
                 $parsed = [];
                 parse_str($raw_input, $parsed);
                 if(is_array($parsed)){
                     $data = array_merge($parsed, $json);
                 }
             }
         }

         foreach($data as $key => $value){
             $request->data->set($key, $value);
         }
         
     	 parent::handle($request);
     }

     private function normalize_files(array $files){
         $normalized_files = [];
         foreach($files as $fkey => $fval){
             $tmp_fval = [];
             if(is_array($fval['name'])){
                 foreach($fval['name'] as $i => $n){
                     if($fval['error'][$i] == UPLOAD_ERR_OK){
                        $tmp_fval['name'][]      = $fval['name'][$i];
                        $tmp_fval['full_path'][] = $fval['full_path'][$i];
                        $tmp_fval['type'][]      = $fval['type'][$i];
                        $tmp_fval['tmp_name'][]  = $fval['tmp_name'][$i];
                        $tmp_fval['error'][]     = $fval['error'][$i];
                        $tmp_fval['size'][]      = $fval['size'][$i];
                     }
                 }
             }else{
                 if($fval['error'] == UPLOAD_ERR_OK){
                    $tmp_fval['name']      = $fval['name'];
                    $tmp_fval['full_path'] = $fval['full_path'];
                    $tmp_fval['type']      = $fval['type'];
                    $tmp_fval['tmp_name']  = $fval['tmp_name'];
                    $tmp_fval['error']     = $fval['error'];
                    $tmp_fval['size']      = $fval['size'];
                 }
             }
             $normalized_files[$fkey] = $tmp_fval ? $tmp_fval : null;
         }

         return $normalized_files;
     }
}
