<?php
namespace SaQle\Manage;

use SaQle\Migration\Commands\StartProject;
use SaQle/App;

class Cli {
	 private string $command      = '';
	 private array  $arguments    = [];
	 public function __construct($args){
	 	 print_r($args);
	 	 $app = App::init();
	 	 $app::cli_bootstrap();
	 	 $this->command = $args[1] ?? null;
	 	 $this->arguments = match($this->command){
	 	 	'start:project'    => $this->extract_startproject_args($args),
	 	 	default            => throw new \Exception("Unknown command!")
	 	 };
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

	 private function extract_startproject_args(array $args){
	 	 $expected_short = ['-n'];
	 	 $expected_long  = ['--name'];
	 	 return $this->extract_args($expected_short, $expected_long, $args);
	 }

	 public function __invoke(){
		 switch ($this->command){
		     case 'start:project':
	             $name = $this->arguments['name'] ?? null;
			     resolve(StartProject::class)->execute($name);
			 break;
			 default:
			     throw new \Exception("Unknown command!");
			 break;
		 }
	 }
}
