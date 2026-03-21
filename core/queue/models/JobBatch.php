<?php
namespace SaQle\Core\Queue\Models;

use SaQle\Orm\Entities\Model\Schema\{Model, Table};

class JobBatch extends Model {

	 protected function table_schema(Table $table) : void {
	 	 
	 	 $table->fields([
		     'total_jobs' => Table::integer_field()->size('medium'),
		     'pending_jobs' => Table::integer_field()->size('medium'),
		     'failed_jobs' => Table::integer_field()->size('medium'),
		     'created_at' => Table::datetime_field(),
	 	 ]);

	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
	 }

}
