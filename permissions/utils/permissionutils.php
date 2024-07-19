<?php
namespace SaQle\Permissions\Utils;

trait PermissionUtils{
    /**
      * Evaulate a list of permissions
      * @param array $permission_classes: The list of permissions to evaluate
      * @param bool  $absolute          : If set to true, method returns true if and only if all the permissions have been passed
      *                                   If set to false, method returns true if any permission is passed
      *                                   Method returns false if all the permissions have failed
      * */
     public function evaluate_permissions(array $permission_classes, bool $absolute, $request){
         $has_permissions = [];
         $one_passed      = false;
         $redirect_url    = null;
         for($p = 0; $p < count($permission_classes); $p++){
             $permission_mask = $permission_classes[$p];
             $permission_mask_arguments = [];
             if(is_array($permission_classes[$p])){
                 $permission_mask = array_keys($permission_classes[$p])[0];
                 $permission_mask_arguments = array_values($permission_classes[$p])[0];
             }

             $permissions_array = explode("||", $permission_mask);
             $passed            = false;

             if( count($permissions_array) > 1 ){
                 $passed = $this->evaluate_permissions($permissions_array, false, $request);
             }else{
                 $permission_class    = $permissions_array[0];
                 $permission_instance = new $permission_class($request, ...$permission_mask_arguments);
                 $passed              = $permission_instance->has_permission();
                 $redirect_url        = $permission_instance->get_redirect_url();
             }
             $has_permissions[]       = $passed;
             $one_passed              = $passed;
             if(!$passed && $absolute)
                break;
         }

         if(!$absolute && $one_passed){
            return [true, $redirect_url];
         }

         $result = array_reduce($has_permissions, function($result, $element){return $result && $element;}, true);
         return [$result, $redirect_url];
     }
}
?>