<?php
namespace SaQle\Core\Queue\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};

class FailedJob extends Model {

	 protected function table_schema(Table $table) : void {
	 	 
	 	 $table->fields([
		     'job_id' => Table::uuid_field(),
		     'payload' => Table::text_field()->size('big'),
		     'exception' => Table::text_field()->size('big'),
		     'failed_at' => Table::datetime_field()
	 	 ]);

	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
	 }

}
