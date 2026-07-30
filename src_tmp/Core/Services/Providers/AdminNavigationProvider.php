<?php

namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Admin\Admin;
use SaQle\Routes\Resources\ResourceRouteUtils;
use SaQle\App\App;
use SaQle\Auth\Context\ActorContext;

class AdminNavigationProvider extends ServiceProvider {

     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(App $app){
         parent::__construct($app);
         $this->__utilsConstruct();
     }

     public function register(): void {

         $resources = $this->get_resource_links();

         Admin::__navigation(function($nav) use ($resources) {

             $nav->group("Resources", function($group) use ($resources) {

                 $group->link(
                     label: 'Overview', 
                     url:    ActorContext::is_platform() ? 
                             '/saqle/resources/overview' : '/'.config('admin.routes.prefix', "_admin").'/resources/overview/',
                     icon:   'grid-2x2'
                 );

                 foreach($resources as $r){
                     $group->link(
                         label: $r->ui_label, 
                         url:   $r->url,
                         icon: 'boxes'
                     );
                 }


             });

         });
     }
}

