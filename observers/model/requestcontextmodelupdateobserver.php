<?php
namespace SaQle\Observers\Model;

use SaQle\Orm\Entities\Model\Observer\ModelOperationObserver;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;

class RequestContextModelUpdateObserver extends ModelOperationObserver {
     public function handle(IOperationManager $manager){
         $status = $manager->status();
         $model  = $status->data['model'];
         if($model === AUTH_MODEL_CLASS && $status->data['result']){
             $result = $status->data['result'];
             $request = resolve('request');
             $object = is_array($result) ? 
             array_find($result, function($value){
                return $value->user_id === $request->user->user_id;
             }) : ($result->user_id === $request->user->user_id ? $result : null);

             if($object){
                 $user = $model::get()->with(['country'])->where('user_id', $object->user_id)->first();
                 $request->context->set('user', $user, true);
             }
         }
     }
}
