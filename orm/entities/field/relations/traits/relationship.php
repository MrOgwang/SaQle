<?php
namespace SaQle\Orm\Entities\Field\Relations\Traits;

trait Relationship{
       //the class name of the foreign key model
	 public protected(set) string $fmodel {
	 	 set(string $value){
	 	 	 $this->fmodel = $value;
	 	 }

	 	 get => $this->fmodel;
	 }

       //the class name of the primary key model
	 public ?string $pmodel = null {
	 	 set(?string $value){
	 	 	 $this->pmodel = $value;
	 	 }

	 	 get => $this->pmodel;
	 }

        //the name of the primary key
	 public protected(set) ?string $pk = null {
	 	 set(?string $value){
	 	 	 $this->pk = $value;
	 	 }

	 	 get => $this->pk;
	 }

        //the name of the foreign key
	 public protected(set) ?string $fk = null {
	 	 set(?string $value){
	 	 	 $this->fk = $value;
	 	 }

	 	 get => $this->fk;
	 }

        //whether this is a navigation field
	 public protected(set) bool $navigation = false {
	 	 set(bool $value){
	 	 	 $this->navigation = $value;
	 	 }

	 	 get => $this->navigation;
	 }

	 //whether mutliple or not
	 public protected(set) bool $multiple = false {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }

	 //the name to assign the fetch results to
	 public protected(set) ?string $field = null {
	 	 set(?string $value){
	 	 	 $this->field = $value;
	 	 }

	 	 get => $this->field;
	 }

	 //whether to eager feth related field or not
	 public protected(set) bool $eager = false {
	 	 set(bool $value){
	 	 	 $this->eager = $value;
	 	 }

	 	 get => $this->eager;
	 }
}
?>