<?php
namespace SaQle\Auth\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Auth\Permissions\Guard;
use SaQle\Auth\Models\BaseUser;

class AuthorizationProvider extends ServiceProvider {
     public function register(): void {
         Guard::define('is-authenticated', function(?BaseUser $user = null){
             return $user ? true : false;
         });
     }
}
?>
