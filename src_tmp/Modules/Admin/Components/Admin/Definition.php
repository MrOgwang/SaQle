<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Core\Components\Abstracts\AbstractDefinition;
use SaQle\Http\Request\Request;
use SaQle\Admin\{
     Admin,
     Platform
};
use SaQle\Auth\Context\ActorContext;

final class Definition extends AbstractDefinition {

     public function dependencies() : array {
         return [
             'scripts' => [
                 '~https://unpkg.com/lucide@latest'
             ],
         ];
     }

     protected function initialize(array $props = []) : void { 

         parent::initialize($props);

         $this->context()->props()->set(
             'is_platform', 
             ActorContext::is_platform()
         );

         $this->context()->props()->set(
             'navigation', 
             ActorContext::is_platform() ? Platform::navigation() : Admin::navigation()
         );

         $this->context()->props()->set(
             'tenant_name', 
             config('tenancy.enabled') && request()->tenant ? request()->tenant->tenant_name : ""
         );
     }
}