<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\{
     ComponentCompiler,
     RouteCompiler, 
     EventCompiler,
     TemplateCompiler,
     ModelCompiler
};
use SaQle\Core\Support\Cli;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

class BuildProject extends Command {

     public function signature(): Signature {
         return Signature::make()
         ->argument(
             name: 'type',
             required: false,
             default: 'all',
             description: 'Specifies which part of the application to build'
          );
     }

     public function handle(CommandContext $context) : int {

         $type = $context->argument('type', 'all');

         switch($type){
             case "routes":
                 RouteCompiler::compile();
             break;
             case "templates":
                 TemplateCompiler::compile();
             break;
             case "components":
                 ComponentCompiler::compile();
             break;
             case "models":
                 ModelCompiler::compile();
             break;
             case "events":
                 EventCompiler::compile();
             break;
             case "all":
                 Cli::print("Building project...\n");
                
                 //compile components
                 ComponentCompiler::compile();

                 //compile templates
                 TemplateCompiler::compile();

                 //compile models
                 ModelCompiler::compile();

                 //compile routes
                 RouteCompiler::compile();
                 
                 //compile events
                 EventCompiler::compile();

                 Cli::print("Build complete...\n");
             break;
             case "resources":
                 //build resources here
             break;
         }

         return 0;
     }
}
