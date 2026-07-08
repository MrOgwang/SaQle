<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Ui\Template;
use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Http\Request\Request;

class TemplateServiceProvider extends ServiceProvider {
     public function register(): void {

         //define template resolvers
         Template::resolver('saqle.formcontrol', function(Request $request, array $props){

             $type = $props['field']->type;

             return match($type){
                 'checkbox', 'color', 'date', 'datetime-local',
                 'email', 'file', 'hidden', 'month', 'number',
                 'password', 'radio', 'range', 'search', 'select',
                 'tel', 'textarea', 'text', 'time', 'url', 'week' => "formcontrol.{$type}",
                 default                                          => 'formcontrol'
             };
         });

         Template::resolver('saqle.autoresource', function(Request $request, array $props){

             if($request->route->compiled_target->name === 'saqle.autoresource'){
                 $method = $request->route->compiled_target->method;

                 return match($method){
                     'list_resources'   => 'autoresource.table',
                     'show_create_form' => 'autoresource.form',
                     'show_resource'    => 'autoresource.view',
                     'show_edit_form'   => 'autoresource.form',
                     default            => 'autoresource'
                 };
             }

             return 'autoresource.form';
         });
     }
}