<?php
namespace SaQle\Manage;

use SaQle\Migration\Commands\{MakeMigrations, Migrate, MakeCollections, MakeModels, MakeThroughs, SeedDatabase};
use SaQle\Services\Container\Cf;
use Psr\Container\ContainerInterface;

class Manage{
	 private string $command      = '';
	 private array  $arguments    = [];
	 private string $project_root = '';
	 private ContainerInterface $container;
	 public function __construct($args){
	 	 $this->command = $args[1] ?? null;
	 	 $this->arguments = match($this->command){
	 	 	'make:migrations'  => $this->extract_makemigrations_args($args),
	 	 	'migrate'          => [],
	 	 	'make:collections' => $this->extract_makemodels_args($args),
	 	 	'make:models'      => $this->extract_makemodels_args($args),
	 	 	'make:throughs'    => $this->extract_makemodels_args($args),
	 	 	'db:seed'          => [],
	 	 	default            => throw new \Exception("Unknown command!")
	 	 };
	 	 $this->project_root = $args[ count($args) - 1];
	 	 $this->container = Cf::create(ContainerInterface::class);
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

	 private function extract_makemigrations_args(array $args){
	 	 $expected_short = ['-n', '-c', '-a'];
	 	 $expected_long  = ['--name', '--context', '--app'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 private function extract_makemodels_args(array $args){
	 	 $expected_short = ['-c', '-a'];
	 	 $expected_long  = ['--context', '--app'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 public function __invoke(){
		 switch ($this->command){
		     case 'make:migrations':
	             $migration_name = $this->arguments['name']    ?? null;
	             $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
		         if ($migration_name){
		             (Cf::create(MakeMigrations::class))->execute($migration_name, $this->project_root, $app_name, $db_context);
		         }else{
		             throw new \Exception("Please provide a migration name!");
		         }
			 break;
			 case 'migrate':
			     (Cf::create(Migrate::class))->execute($this->project_root);
			 break;
			 case 'make:backoffice':
			     //(Cf::create(MakeBackoffice::class))->execute($this->project_root);
			 break;
			 case 'make:collections':
	             $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
			     (Cf::create(MakeCollections::class))->execute($this->project_root, $app_name, $db_context);
			 break;
			 case 'make:models':
			     $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
			     (Cf::create(MakeModels::class))->execute($this->project_root, $app_name, $db_context);
			 break;
			 case 'make:throughs':
			     $app_name       = $this->arguments['app']     ?? null;
	             $db_context     = $this->arguments['context'] ?? null;
			     (Cf::create(MakeThroughs::class))->execute($this->project_root, $app_name, $db_context);
			 break;
			 case 'db:seed':
			     (Cf::create(SeedDatabase::class))->execute($this->project_root);
			 break;
			 default:
			     throw new \Exception("Unknown command!");
			 break;
		 }
	 }
}
?>