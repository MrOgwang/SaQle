<?php
namespace SaQle\Auth\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Auth\Exceptions\{
     AuthenticationException,
     AuthorizationException
};
use SaQle\Auth\Identity\User\Interfaces\UserInterface;
use SaQle\Auth\Services\AuthenticationService;

class PlatformAuthorizationProvider extends ServiceProvider {
     public function register(): void {

         $this->app->guards->add(
             '__authenticated__', 

             function(?UserInterface $user = null){
                 return $user ? true : false;
             },

             function($request){ 
                 if($request->is_web_request()){
                     redirect(route('saqle.login.form'));
                 }

                 throw new AuthenticationException('Platform user not authenticated!');
             }
         );

         $this->app->guards->add(
             '__super_admin__', 

             function(?UserInterface $user = null){
                 return $user->is_super_admin ? true : false;
             },

             function($request){ 
                 if($request->is_web_request()){
                     $auth_service = resolve(AuthenticationService::class);
                     $auth_service->logout();
                     redirect(route('saqle.login.form'));
                 }

                 throw new AuthorizationException('User not authorized to access this resource!');
             }
         );

     }
}
?>
