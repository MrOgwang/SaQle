<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Listeners\Model\UpdateSessionUser;

class EventServiceProvider extends ServiceProvider {
     private function get_model_name(string $model_class_name){
         $parts = explode('\\', $model_class_name);
         return end($parts);
     }

     public function register(): void {
         $this->app->events->add($this->get_model_name(AUTH_MODEL_CLASS)."::updated", [
             UpdateSessionUser::class
         ]);
     }
}

