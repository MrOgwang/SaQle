<?php
namespace SaQle\Manage;

use SaQle\Migration\Commands\MakeMigrations;
use SaQle\Migration\Commands\Migrate;
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
	 	 	'makemigrations' => $this->extract_makemigrations_args($args),
	 	 	'migrate'        => [],
	 	 	default          => throw new \Exception("Unknown command!")
	 	 };
	 	 $this->project_root = $args[ count($args) - 1];
	 	 $this->container = Cf::create(ContainerInterface::class);
	 }

	 private function extract_makemigrations_args(array $args){
	 	 $expected_short = ['-n', '-c', '-a'];
	 	 $expected_long  = ['--name', '--context', '--app'];
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

	 public function __invoke(){
		 switch ($this->command){
		     case 'makemigrations':
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
			 default:
			     throw new \Exception("Unknown command!");
			 break;
		 }
	 }
}
?>