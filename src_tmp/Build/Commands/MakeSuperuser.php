<?php

namespace SaQle\Build\Commands;

use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Auth\Interfaces\UserRegistrationInterface;
use SaQle\Core\Support\Cli;
use SaQle\Build\Utils\MakeUserUtils;
use Exception;

class MakeSuperuser extends Command {

     use MakeUserUtils;

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         print_r($context);

         /*try{
             Cli::print("Creating super user account!");

             $model_class = config('auth.model_class');

             $data = $this->collect_user_data($model_class);

             $has_register_service = app()->container->has(UserRegistrationInterface::class);

             if($has_register_service){
                 $service = resolve(UserRegistrationInterface::class);
                 $service->register(...$data);
             }else{
                 $model_class::create($data)->now();
             }

             Cli::print("Super user was created successfully!");
         }catch(Exception $e){
             Cli::print("ERROR:");
             Cli::print($e->getMessage());
         }*/

         return 0;
     }
}
