<?php
namespace SaQle\Listeners\Model;

use SaQle\Auth\Middleware\AuthenticationMiddleware;
use SaQle\Core\Events\GenericEvent;
use SaQle\Http\Request\Request;
use SaQle\Orm\Entities\Model\Collection\ModelCollection;
use SaQle\Orm\Entities\Model\Schema\Model;

class UpdateSessionUser {
     public function __construct(
         private Request $request,
         private AuthenticationMiddleware $auth_middleware
     ){}

     public function handle(GenericEvent $event): void {
         
         $result = $event->context->result();

         $session_user = $event->context->user();

         if($session_user){

             $user = $result instanceof ModelCollection ? array_find($result->items(), function($u) use ($session_user){
                 return $u->user_id === $session_user->user_id;
             }) : ($result->user_id === $session_user->user_id ? $result : null);

             if($user){
                 $this->$auth_middleware->handle($this->request);
             }
         }
     }
}
