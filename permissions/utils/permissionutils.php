<?php
namespace SaQle\Permissions\Utils;

use SaQle\Services\Container\Cf;

trait PermissionUtils{
    private static function at_least_one_passed($results){
        return in_array(true, $results);
    }

    private static function all_passed($results){
        return array_reduce($results, function($result, $element){return $result && $element;}, true);
    }

    /**
      * Evaulate a list of permissions
      * @param array $permission_classes: The list of permissions to evaluate
      * @param bool  $absolute          : If set to true, method returns true if and only if all the permissions have been passed
      *                                   If set to false, method returns true if any permission is passed
      *                                   Method returns false if all the permissions have failed
      * */
     public static function evaluate_permissions(array $permission_classes, bool $absolute){
         $has_permissions = [];
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
                 [$group_passed, $group_redirect_url]  = self::evaluate_permissions($permissions_array, false);
                 $passed = $group_passed;
                 if(!$group_passed && $group_redirect_url){
                     $redirect_url = $group_redirect_url;
                 }
             }else{
                 $permission_class    = $permissions_array[0];
                 $permission_instance = new $permission_class(...$permission_mask_arguments);
                 $passed              = $permission_instance->has_permission();
                 $this_redirect_url   = $permission_instance->get_redirect_url();
                 if(!$passed && $this_redirect_url){
                     $redirect_url    = $this_redirect_url;
                 }
             }
             $has_permissions[]       = $passed;
         }

         if(!$absolute && self::at_least_one_passed($has_permissions)){
            return [true, $redirect_url];
         }
         
         return [self::all_passed($has_permissions), $redirect_url];
     }
}
