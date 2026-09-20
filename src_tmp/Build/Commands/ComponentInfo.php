<?php

namespace SaQle\Build\Commands;

use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Core\Support\Cli;
use SaQle\Core\Registries\ComponentRegistry;
use Exception;

class ComponentInfo extends Command {

     public function signature(): Signature {
         return Signature::make()
         ->argument(
             name: 'name',
             required: true,
             description: 'The name of the component to get info'
         );
     }

     public function handle(CommandContext $context) : int {

         try{

             $name = $context->argument('name');

             $context->output()->info("\nComponent information for: {$name}\n");

             $component = ComponentRegistry::get_or_fail($name);

             $this->display($name, $component);

         }catch(Exception $e){
             $context->output()->error($e->getMessage());
         }

         return 0;
     }

     private function display(string $component, array $definition): void {

         $context = command();

         $context->output()->line('');
         $context->output()->line('Component Information');
         $context->output()->line(str_repeat('─', 40));
         $context->output()->line('');

         $context->output()->field('Name', $component);
         $context->output()->field('Owner', $definition['owner'] ?? null);
         $context->output()->field('Base Path', $definition['base_path'] ?? null);

         $context->output()->line('');

         $context->output()->field('Controller', $definition['controller'] ?? null);
         $context->output()->field('Controller Path', $definition['controller_path'] ?? null);
         $context->output()->field('Definition', $definition['definition'] ?? null);
         $context->output()->field('Definition Path', $definition['definition_path'] ?? null);
         $context->output()->field('Template', $definition['template_path'] ?? null);
         $context->output()->field('Compiled Template', $definition['compiled_template_path'] ?? null);
         $context->output()->field('Style', $definition['style_path'] ?? null);
         $context->output()->field('Script', $definition['script_path'] ?? null);
         $context->output()->field('Proxy', $definition['proxy'] ?? null);

         $context->output()->field(
             'Has Many Templates',
             !empty($definition['has_many_templates']) ? 'Yes' : 'No'
         );

         $this->display_templates($definition['template_variations'] ?? []);

         $context->output()->line('');
     }

     private function display_templates(array $templates): void {
        
         $context = command();

         if(empty($templates)){
             return;
         }

         $context->output()->line('');
         $context->output()->line('Templates');
         $context->output()->line(str_repeat('─', 40));

         foreach($templates as $name => $template){
             $context->output()->line("  {$name}");

             $context->output()->line(
                 '    ' . str_pad('Source', 16) .
                 ($template['template_path'] ?? '—')
             );

             $context->output()->line(
                 '    ' . str_pad('Compiled', 16) .
                 ($template['compiled_template_path'] ?? '—')
             );
         }
     }

}
