<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Database\Schema;
use SaQle\Core\Migration\Models\{
	 Migration,
	 TenantMigration
};
use SaQle\Session\Models\Session;
use SaQle\Core\Queue\Models\{
	 FailedJob, 
	 Job, 
	 JobBatch
};
use SaQle\Auth\Models\PlatformUser;

class SystemSchema extends Schema {

	 protected function models() : array {
	 	 return [
	  	     config('auth.model_class'),
	 	 	 config('tenancy.model_class'),
	 	 	 Migration::class,
	 	 	 TenantMigration::class,
	 	 	 Session::class,
	 	 	 FailedJob::class,
	 	 	 Job::class,
	 	 	 JobBatch::class
	 	 ];
	 }

}
?>