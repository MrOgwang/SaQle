<?php

namespace SaQle\Build\Commands;

use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Build\Utils\ComponentOverrider;
use SaQle\Core\Registries\ComponentRegistry;
use Exception;

class OverrideComponent extends Command {

     public function signature(): Signature {
         return Signature::make()
         ->argument(
             name: 'name',
             required: true,
             description: 'The name of the component to publish'
         );
     }

     public function handle(CommandContext $context) : int {

         $name = $context->argument('name');

         try{

             $context->output()->info("\nOverriding component: {$name}\n");

             $component = ComponentRegistry::get_or_fail($name);

             $source_path = $component['source_path'] ?? "";

             $source = $component['source_owner'] === 'project' ? 
             path_join([config('base_path'), $source_path]) : 
             path_join([config('framework_path'), $source_path]);

             $context->output()->info("Checking source: {$source}\n");

             if(!$source || !is_dir($source)){
                 throw new Exception("Component source directory does not exist.");
             }

             $destination = ComponentOverrider::override($name, $source, $context);

             $context->output()->success("Component '{$name}' override successful.");
             $context->output()->line('');
             $context->output()->field('Source', $source);
             $context->output()->field('Destination', $destination);

         }catch(Exception $e){
             $context->output()->error("Unable to publish component '{$name}'.");

             $context->output()->error($e->getMessage());

             return 1;
         }

         return 0;
     }
}
