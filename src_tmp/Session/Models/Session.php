<?php
namespace SaQle\Session\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};
use SaQle\Orm\Entities\Model\Interfaces\ISystemModel;

class Session extends Model implements ISystemModel {

	 protected function table_schema(Table $table) : void {
	 	 
	 	 $table->fields([
		     'session_id' => Table::char_field()->max_length(100)->required(),
		     'session_data' => Table::text_field()
	 	 ]);

	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
	 }

}
