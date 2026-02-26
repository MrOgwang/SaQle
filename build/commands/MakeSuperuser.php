<?php
namespace SaQle\Build\Commands;

class MakeSuperuser{
     
     private function make_superuser(string $project_root, $email, $password){
         $model_class_schema = config('auth.model_class');
         $model_class        = $model_class_schema;
         $user               = (new $model_class(...[
            'username'       => $email,
            'password'       => md5($password),
            'first_name'     => 'Super',
            'last_name'      => 'User',
            'label'          => 'SUPER',
            'gender'         => 'male',
            'dob'            => '1993-08-15',
            'is_online'      => 0,
            'account_status' => 3,
            'disabled'       => 0
         ]))->save();
         if($user){
             echo "Super user created!\n";
         }
     }

     public function execute($project_root, $email, $password){
           $this->make_superuser($project_root, $email, $password);
     }
}
