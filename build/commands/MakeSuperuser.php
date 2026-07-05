<?php
namespace SaQle\Build\Commands;

use SaQle\Auth\Interfaces\UserRegistrationInterface;
use SaQle\Core\Support\Cli;
use SaQle\Orm\Entities\Field\Types\{
     CharChoiceField,
     IntegerChoiceField
};
use SaQle\Auth\Models\PlatformUser;
use SaQle\Build\Utils\MakeUserUtils;
use Exception;

class MakeSuperuser {

     use MakeUserUtils;
     
     public function execute(){
         try{
             Cli::print("Creating super user account!");

             $data = $this->collect_user_data(PlatformUser::class);

             PlatformUser::create($data)->now();
             
             Cli::print("Super user was created successfully!");
         }catch(Exception $e){
             Cli::print("ERROR:");
             Cli::print($e->getMessage());
         }
     }
}
