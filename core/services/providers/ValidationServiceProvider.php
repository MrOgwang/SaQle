<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Security\Validation\Validators\{
     MinLengthValidator
};

class ValidationServiceProvider extends ServiceProvider {
     public function register(): void {
         $this->app->rules->add('min_length', MinLengthValidator::class);
     }
}

