<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Schema\{
	 Model, 
	 Table
};
use SaQle\Auth\Identity\Tenant\Interfaces\TenantInterface;
use SaQle\Orm\Entities\Model\Interfaces\ISystemModel;

class BaseTenant extends Model implements ISystemModel, TenantInterface {

	 protected function table_schema(Table $table) : void {

	 	 $table->primary_key('tenant_id');

		 $table->fields([ 
		     'tenant_name' => Table::char_field()->required(),
		 ]);

		 $table->with_user_audit(false);
		 $table->with_timestamps(true);
		 $table->with_soft_delete(false);
	 } 

     public function get_id() : mixed {
     	 return $this->tenant_id;
     }

     public function get_name() : string {
     	 return $this->tenant_name;
     }
}
