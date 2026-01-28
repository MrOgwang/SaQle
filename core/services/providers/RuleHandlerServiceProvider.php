<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Security\Validation\Types\ValueType;
use SaQle\Security\Validation\Handlers\Max\{FileMaxHandler, TextMaxHandler, NumberMaxHandler};

class RuleHandlerServiceProvider extends ServiceProvider {
     public function register(): void {

         //add maximum handlers
         $this->app->rules->add('max', ValueType::NUMBER, new NumberMaxHandler());
         $this->app->rules->add('max', ValueType::TEXT, new TextMaxHandler());
         $this->app->rules->add('max', ValueType::FILE, new FileMaxHandler());

     }
}

