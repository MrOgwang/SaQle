<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Interfaces\ISystemModel;
use SaQle\Orm\Entities\Model\Schema\Table;

class PlatformUser extends BaseUser implements ISystemModel{
	 protected function table_schema(Table $table) : void {
	 	
	 	 $table->fields([ 
		     'is_super_user' => Table::boolean_field()->required()->render(function($value, $model){
			 	 return $value ? 'Yes' : 'No';
			 })->default(false)
		 ]);

		 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);

		 parent::table_schema($table);
	 }
}
