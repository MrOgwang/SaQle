<?php
namespace SaQle\Listeners\Model;

use SaQle\Auth\Middleware\AuthenticationMiddleware;
use SaQle\Core\Events\GenericEvent;

/**
 * Save file uploads after a model is created in the database
 * */
class SaveUploadedFiles {
     public function handle(GenericEvent $event): void {
         $model_instance = $event->context->service();
         $files          = $event->context->arg('files') ?? [];

         $result = $event->context->result();
         $session_user = $event->context->user();

         if($session_user){
             $user = is_array($result) ?  array_find($result, function($u){
                 return $u->user_id === $session_user->user_id;
             }) : ($result->user_id === $session_user->user_id ? $result : null);

             if($user){
                 $request = resolve('request');
                 new AuthenticationMiddleware()->handle($request);
             }
         }
     }
}
