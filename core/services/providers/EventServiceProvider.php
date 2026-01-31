<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Listeners\Model\{UpdateSessionUser, SaveUploadedFiles, RemoveUploadedFiles};

class EventServiceProvider extends ServiceProvider {
     private function get_model_name(string $model_class_name){
         $parts = explode('\\', $model_class_name);
         return end($parts);
     }

     public function register(): void {

         //this event updates the session user automatically
         $this->app->events->add($this->get_model_name(config('auth_model_class'))."::updated", [
             UpdateSessionUser::class
         ]);

         //this event will move uploaded files to final location after insert
         $this->app->events->add("::created", [
             SaveUploadedFiles::class
         ]);

         //this event will move uploaded files to final location after update
         $this->app->events->add("::updated", [
             SaveUploadedFiles::class
         ]);

         //this event will delete model files after a model is deleted
         $this->app->events->add("::deleted", [
             RemoveUploadedFiles::class
         ]);
     }
}

