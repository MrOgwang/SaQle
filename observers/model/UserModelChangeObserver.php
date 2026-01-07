<?php
namespace SaQle\Observers\Model;

use SaQle\Core\Observable\Observer;
use SaQle\Auth\Middleware\AuthenticationMiddleware;

class UserModelChangeObserver implements Observer {
     public function update_user($result){
         $request = resolve('request');
         $user = is_array($result) ?  array_find($result, function($u){
             return $u->user_id === $request->user->user_id;
         }) : ($result->user_id === $request->user->user_id ? $result : null);

         if($user){
             new AuthenticationMiddleware()->handle($request);
         }
     }
}
