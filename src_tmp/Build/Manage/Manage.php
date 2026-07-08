<?php
namespace SaQle\Build\Manage;

use SaQle\Build\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, 
	MakeThroughs, SeedDatabase, ResetDatabase, MakeSuperuser, StartProject, 
	StartApps, MakeResources, BuildProject, TestModel, RunCron, QueueCron,
	MakeComponent, MakeUser, Install, MakeEnv, MigrateStructure
};
use SaQle\Build\Utils\MigrationUtils;
use Exception;
use SaQle\Core\Support\ActorContext;
use SaQle\Auth\Models\CliUser;

class Manage {
	 private string $command      = '';
	 private array  $arguments    = [];
	 private string $project_root = '';
	 public function __construct($args){
	 	 $this->command = $args[1] ?? null;
	 	 $this->arguments = match($this->command){
	 	 	'make:component'   => $this->extract_makecomponent_args($args),
	 	 	'make:migrations'  => $this->extract_makemigrations_args($args),
	 	 	'make:resources'   => [],
	 	 	'migrate'          => [],
	 	 	'make:collections' => $this->extract_makemodels_args($args),
	 	 	'make:models'      => $this->extract_makemodels_args($args),
	 	 	'make:throughs'    => $this->extract_makemodels_args($args),
	 	 	'make:superuser'   => [],
	 	 	'make:user'        => [],
	 	 	'db:seed'          => [],
	 	 	'db:reset'         => [],
	 	 	'start:project'    => $this->extract_startproject_args($args),
	 	 	'start:apps'       => $this->extract_startapps_args($args),
	 	 	'build'            => $this->extract_build_args($args),
	 	 	'model:test'       => [],
	 	 	'run:cron'         => [],
	 	 	'queue:cron'       => [],
	 	 	'install'          => [],
	 	 	'rename'           => [],
	 	 	default            => throw new Exception("Unknown command!")
	 	 };
	 	 $this->project_root = config('base_path');
	 }

	 private function extract_args(array $expected_short, array $expected_long, array $args){
	 	 $extracted_args = [];
	 	 for($x = 2; $x < count($args); $x++){
	 	 	 $arg_parts = explode("=", $args[$x]);
	 	 	 if(in_array($arg_parts[0], $expected_short) || in_array($arg_parts[0], $expected_long)){
	 	 	 	 $key_index = array_search($arg_parts[0], $expected_short);
	 	 	 	 if($key_index === false){
	 	 	 	 	$key_index = array_search($arg_parts[0], $expected_long);
	 	 	 	 }
	 	 	 	 $key = str_replace("-", "", $expected_long[$key_index]);
	 	 	 	 $extracted_args[$key] = $arg_parts[1];
	 	 	 }
	 	 }
	 	 return $extracted_args;
	 }

	 private function extract_makecomponent_args(array $args){
	 	 $expected_short = ['-n', '-m', '-p'];
	 	 $expected_long  = ['--name', '--module', '--proxy'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_makemigrations_args(array $args){
	 	 $expected_short = ['-n'];
	 	 $expected_long  = ['--name'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_build_args(array $args){
	 	 $expected_short = ['-t'];
	 	 $expected_long  = ['--type'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_makemodels_args(array $args){
	 	 $expected_short = ['-c', '-a'];
	 	 $expected_long  = ['--context', '--app'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_startproject_args(array $args){
	 	 $expected_short = ['-n'];
	 	 $expected_long  = ['--name'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_startapps_args(array $args){
	 	 $expected_short = ['-n'];
	 	 $expected_long  = ['--name'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function bootstrap() : void {
         date_default_timezone_set(config('app.timezone'));

	     //Default CLI actor
	     ActorContext::set_actor(new CliUser);
	 }

	 public function __invoke(){

	 	 $this->bootstrap();

		 switch ($this->command){
		 	 case 'make:component':
	             $name = $this->arguments['name'] ?? null;
	             $module = $this->arguments['module'] ?? null;
	             $proxy = array_key_exists('proxy', $this->arguments) ? true : false;

	             if(!$name){
	             	 throw new Exception("Please provide a component name!");
	             }

	             MakeComponent::execute($name, $module, $proxy);
			 break;
		     case 'make:migrations':
	             $migration_name = $this->arguments['name'] ?? null;

	             if(!$migration_name){
	             	 throw new Exception("Please provide a migration name!");
	             }

	             resolve(MakeMigrations::class)->execute($migration_name);
			 break;
			 case 'migrate':
			     resolve(Migrate::class)->execute();
			 break;
			 case 'make:backoffice':
			     //resolve(MakeBackoffice::class)->execute($this->project_root);
			 break;
			 case 'make:collections':
	             $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
			     resolve(MakeCollections::class)->execute($this->project_root, $app_name, $db_context);
			 break;
			 case 'make:models':
			     $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
			     resolve(MakeModels::class)->execute($this->project_root, $app_name, $db_context);
			 break;
			 case 'make:throughs':
			     resolve(MakeThroughs::class)->execute();
			 break;
			 case 'make:superuser':
			     resolve(MakeSuperuser::class)->execute();
			 break;
			 case 'make:user':
			     resolve(MakeUser::class)->execute();
			 break;
			 case 'db:seed':
			     resolve(SeedDatabase::class)->execute();
			 break;
			 case 'db:reset':
			     resolve(ResetDatabase::class)->execute();
			 break;
			 case 'start:project':
			     $name = $this->arguments['name'] ?? null;
			     resolve(StartProject::class)->execute($name);
			 break;
			 case 'start:apps':
			     $name = $this->arguments['name'] ?? null;
			     resolve(StartApps::class)->execute($this->project_root, $name);
			 break;
			 case 'build':
			     $type = $this->arguments['type'] ?? 'all';
			     resolve(BuildProject::class)->execute($type);
			 break;
			 case 'make:resources':
			     MakeResources::execute($this->project_root);
			 break;
			 case 'model:test':
			     TestModel::execute();
			 break;
			 case "run:cron":
			     RunCron::execute();
			 break;
			 case "queue:cron":
			     QueueCron::execute();
			 break;
			 case "install":
			     Install::execute();
			 break;
			 case "make:env":
			     MakeEnv::execute();
			 break;
			 case 'rename':
			     MigrateStructure::execute();
			 break;
			 default:
			     throw new Exception("Unknown command!");
			 break;
		 }
	 }
}
