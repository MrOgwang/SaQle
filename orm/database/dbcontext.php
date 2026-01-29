<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Core\Migration\Models\Migration;
use SaQle\Auth\Models\{Login, Contact, Vercode};
use SaQle\Session\Models\Session;
use SaQle\Orm\Entities\Model\TempId;
use SaQle\Orm\Entities\Model\Interfaces\{IThroughModel, ITempModel};

abstract class DbContext{
	 protected array $models = [];
	 protected bool  $include_auth_models = false;
	 protected bool  $include_rbac_models = false;
	 protected bool  $include_tenant_models = false;

	 public function __construct(){
	 	 $this->models = array_merge($this->models, [
	 	 	 'migrations' => Migration::class,
	 	 	 'model_temp_ids' => TempId::class
	 	 ]);

	 	 if(config('enable_rbac') && $this->include_rbac_models){
	 	 	 $this->models['roles']           = config('role_model_class');
	 	 	 $this->models['permissions']     = config('permission_model_class');
	 	 	 $this->models['userroles']       = config('user_role_model_class');
	 	 	 $this->models['userpermissions'] = config('user_permission_model_class');
	 	 	 $this->models['rolepermissions'] = config('role_permission_model_class');
	 	 }

	 	 if(config('enable_multitenancy') && $this->include_tenant_models){
	 	 	 $this->models['tenants'] = config('tenant_model_class');
	 	 }

	 	 if($this->include_auth_models){
	 	 	 $this->models['users']             = config('auth_model_class');
		     $this->models['sessions']          = Session::class;
			 $this->models['logins']            = Login::class;
			 $this->models['contacts']          = Contact::class;
			 $this->models['verificationcodes'] = Vercode::class;
	 	 }
	 }

	 public function get_models() : array {
	 	 return $this->models;
	 }

	 public function get_temporary_models() : array {
	 	 $models = [];
	 	 foreach($this->models as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
	 	 	 if(in_array(ITempModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }

	 public function get_permanent_models() : array {
	 	 $models = [];
	 	 foreach($this->models as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
	 	 	 if(!in_array(ITempModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }

	 public function get_through_models() : array {
	 	 $models = [];
	 	 foreach($this->models as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
	 	 	 if(in_array(IThroughModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }
}
