<?php
namespace SaQle\Orm\Entitities\Model\Manager\Containers;

class InsertContainer{
	 //the primary key type
	 public string $pktype = '' {
	 	 set(string $value){
	 	 	 $this->pktype = $value;
	 	 }

	 	 get => $this->pktype;
	 }

	 //an array of data to be inserted during the insert operation
	 public array $data = [] {
	 	 set(string $value){
	 	 	 $this->data = $value;
	 	 }

	 	 get => $this->data;
	 }

     //primary key name
	 public string $pkname = '' {
	 	 set(string $value){
	 	 	 $this->pkname = $value;
	 	 }

	 	 get => $this->pkname;
	 }

	 //primary key values
	 public array $pkvalues = [] {
	 	 set(array $value){
	 	 	 $this->pkvalues = $value;
	 	 }

	 	 get => $this->pkvalues;
	 }

	 //navigational keys in the table being manipulated
	 public array $navkeys = [] {
	 	 set(array $value){
	 	 	 $this->navkeys = $value;
	 	 }

	 	 get => $this->navkeys;
	 }

	 //whether we are inerting multiple values or not
	 public bool $multiple = false {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }
}

?>