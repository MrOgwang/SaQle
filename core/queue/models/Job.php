<?php
namespace SaQle\Core\Queue\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};

class Job extends Model {

	 protected function table_schema(Table $table) : void {
	 	 
	 	 $table->fields([
		     'queue' => Table::char_field()->max_length(50)->required()->default('default'),
		     'payload' => Table::text_field()->size('big'),
		     'attempts' => Table::integer_field()->default(0),
		     'max_attempts' => Table::integer_field()->default(3),
		     'priority' => Table::integer_field()->default(0),
		     'reserved_at' => Table::datetime_field(),
		     'available_at' => Table::datetime_field(),
		     'created_at' => Table::datetime_field()
	 	 ]);

	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
	 }

}
