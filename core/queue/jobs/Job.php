<?php
namespace SaQle\Core\Queue\Jobs;

use Throwable;

abstract class Job {

     protected int $tries = 3;

     protected int $delay = 0;

     protected int $priority = 0;

     protected array $middleware = [];
     
     protected null|string|int $user_id = null;

     abstract public function handle() : void;

     public function failed(Throwable $e) : void {}

     public function middleware() : array {
        return $this->middleware;
     }

     public function with_user(null|string|int $user_id): static {
         $this->user_id = $user_id;
         return $this;
     }

     public function bootContext(): void {
         if($this->user_id){
             $auth_model = config('app.auth_model');
             Auth::setUser(User::find($this->userId));
         }
     }

     public function get_tries() : int { 
         return $this->tries; 
     }

     public function get_delay(): int { 
         return $this->delay; 
     }

     public function get_priority(): int { 
         return $this->priority; 
     }
}