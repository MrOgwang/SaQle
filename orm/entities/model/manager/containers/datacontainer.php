<?php
namespace SaQle\Orm\Entities\Model\Manager\Containers;

class DataContainer{

	 //an array of data to be inserted during the insert operation
	 public array $data = [] {
	 	 set(array $value){
	 	 	 $this->data = $value;
	 	 }

	 	 get => $this->data;
	 }

	 //an array of data to be inserted during the insert operation
	 public array $files = [] {
	 	 set(array $value){
	 	 	 $this->files = $value;
	 	 }

	 	 get => $this->files;
	 }

	 //primary key values
	 public array $pkvalues = [] {
	 	 set(array $value){
	 	 	 $this->pkvalues = $value;
	 	 }

	 	 get => $this->pkvalues;
	 }

	 //whether we are inerting multiple values or not
	 public bool $multiple = false {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }
}

