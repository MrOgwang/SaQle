<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

/**
* This middleware injects data from POST, GET and FILES super globals into the request object.
*/
class DataConsolidatorMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         foreach($_POST as $pkey => $pval){
         	$request->data->set($pkey, $pval);
         }
         foreach($_GET as $gkey => $gval){
         	$request->data->set($gkey, $gval);
         }
         foreach($_FILES as $fkey => $fval){
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
            $request->data->set($fkey, $tmp_fval ? $tmp_fval : '');
         }
         parse_str(file_get_contents('php://input'), $_PATCH);
         if(is_array($_PATCH)){
             foreach($_PATCH as $ekey => $eval){
                 $request->data->set($ekey, $eval);
             }
         }
     	 parent::handle($request);
     }
}
?>