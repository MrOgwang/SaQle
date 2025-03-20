<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Operations\Crud\RunOperation;

class RunManager{
	 private string $sql = {
	 	 set(string $value){
	 	 	 $this->sql = $value;
	 	 }

	 	 get => $this->sql;
	 }

	 private string $operation = {
	 	 set(string $value){
	 	 	 $this->operation = $value;
	 	 }

	 	 get => $this->operation;
	 }

	 private ?array $data = null {
	 	 set(?array $value){
	 	 	 $this->data = $value;
	 	 }

	 	 get => $this->data;
	 }

	 private bool $multiple = true {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }

	 public function __construct(string $sql, string $operation, ?array $data = null, bool $multiple = true){
	 	 $this->sql = $sql;
	 	 $this->operation = $operation;
	 	 $this->data = $data;
	 	 $this->multiple = $multiple;
	 }

	 public function now(){
	 	 /*setup a run command*/
	 	 $this->crud_command = new RunCommand(
	 	 	 new RunOperation($this->get_connection()),
	 	 	 sql:       $sql,
	 	 	 operation: $operation,
	 	 	 data:      $data,
	 	 	 multiple: $multiple,
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
	 }
}
?>