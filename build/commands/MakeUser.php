<?php
namespace SaQle\Build\Commands;

use SaQle\Auth\Interfaces\UserRegistrationInterface;
use SaQle\Core\Support\Cli;
use SaQle\Orm\Entities\Field\Types\{
     CharChoiceField,
     IntegerChoiceField
};
use SaQle\Build\Utils\MakeUserUtils;
use Exception;

class MakeSuperuser {

     use MakeUserUtils;

     public function execute(){
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
         }catch(Exception $e){
             Cli::print("ERROR:");
             Cli::print($e->getMessage());
         }
     }
}
