<?php

namespace SaQle\Build\Commands;

use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Core\Registries\ComponentRegistry;
use Exception;

class ComponentList extends Command {

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         $context->output()->info("\nListing all project components\n");

         $components = ComponentRegistry::all();

         $headers = ['#', 'NAME', 'OWNER', 'PROXY', 'PATH', 'HAS TEMPLATES'];

         $rows = [];

         $count = 0;
         foreach($components as $c => $c_props){

             $count += 1;

             $rows[] = [
                 "#{$count}",
                 $c,
                 $c_props['owner'],
                 $c_props['proxy'] ? "Yes" : "No",
                 $c_props['base_path'],
                 $c_props['has_many_templates'] ? "Yes" : "No"
             ];
         }

         $context->output()->table($headers, $rows);

         return 0;
     }
}
