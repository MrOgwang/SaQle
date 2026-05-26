<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Ui\Template;
use SaQle\Core\Services\Providers\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider {
     public function register(): void {

         //define template resolvers
         Template::resolver('saqle.formcontrol', function(array $props){

             $type = $props['field']->type;

             return match($type){
                 'checkbox', 'color', 'date', 'datetime-local',
                 'email', 'file', 'hidden', 'month', 'number',
                 'password', 'radio', 'range', 'search', 'select',
                 'tel', 'textarea', 'text', 'time', 'url', 'week' => "formcontrol.{$type}",
                 default                                          => 'formcontrol'
             };
         });

     }
}
?>
