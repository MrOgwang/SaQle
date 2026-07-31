<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Listeners\Model\{
     UpdateSessionUser,
     RunTenantMigrations
};

class EventServiceProvider extends ServiceProvider {
     private function get_model_name(string $model_class_name){
         $parts = explode('\\', $model_class_name);
         return end($parts);
     }

     public function register(): void {

         /**
          * This event is fired when the auth model class (User model for most cases)
          * is updated. The update session user listener automatically updates user details
          * */
         $this->app->events->add($this->get_model_name(config('auth.model_class'))."::updated", [
             UpdateSessionUser::class
         ]);

         /**
          * When a tenant is saved into the database, this event listsner will run migrations for
          * that tenant.
          * */
         $this->app->events->add($this->get_model_name(config('tenancy.model_class'))."::created", [
             RunTenantMigrations::class
         ]);
         
     }
}

