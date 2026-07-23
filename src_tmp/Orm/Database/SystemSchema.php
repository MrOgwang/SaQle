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
	
	 public function __construct(){

	 	 $tenant_model = config('tenancy.model_class');
 
	 	 $this->models = [
		 	 'users'    => config('auth.model_class'), //PlatformUser::class,
		 	 'tenants'           => $tenant_model,
		 	 'migrations'        => Migration::class,
		 	 'tenant_migrations' => TenantMigration::class,
		 	 'sessions'          => Session::class,
		 	 'queue_failed_jobs' => FailedJob::class,
		 	 'queue_jobs'        => Job::class,
		 	 'queue_job_batches' => JobBatch::class
		 ];

		 $this->admin_models = [
		 	 'users',
		 	 'tenants'
		 ];
	 }

}
?>