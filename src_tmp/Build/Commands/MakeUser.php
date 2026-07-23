<?php
namespace SaQle\Build\Commands;

use SaQle\Auth\Interfaces\UserRegistrationInterface;
use SaQle\Core\Support\Cli;
use SaQle\Orm\Entities\Field\Types\{
     CharChoiceField,
     IntegerChoiceField
};
use SaQle\Build\Utils\MakeUserUtils;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use Exception;

class MakeUser extends Command {

     use MakeUserUtils;

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         try{

             $model_class = config('auth.model_class');

             $data = $this->collect_user_data($model_class);

             $has_register_service = app()->container->has(UserRegistrationInterface::class);

             if($has_register_service){
                 $service = resolve(UserRegistrationInterface::class);
                 $service->register(...$data);
             }else{
                 $model_class::create($data)->now();
             }

             Cli::print("User was created successfully!");

             return 0;
         }catch(Exception $e){
             Cli::print("ERROR:");
             Cli::print($e->getMessage());

             return 1;
         }
     }
}
