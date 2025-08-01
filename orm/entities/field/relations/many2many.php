<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Define a one to one relationship between two models. To create a complicate relationship
 * between model A and B:
 * In A, have a one to one field pointing to B,
 * In B, have a one to one field pointing to A
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

declare(strict_types = 1);
namespace SaQle\Orm\Entities\Field\Relations;

use SaQle\Orm\Entities\Field\Relations\Base\BaseRelation;

class Many2Many extends BaseRelation{
	
	 //the class name of the through model
	 public protected(set) ?string $through = null {
	 	 set(?string $value){
	 	 	 $this->through = $value;
	 	 }

	 	 get => $this->through;
	 }

	 public function __construct(
	 	 string   $pmodel,
	 	 string   $fmodel,
	 	 ?string  $field      = null, 
	 	 ?string  $pk         = null,
	 	 ?string  $fk         = null,
	 	 bool     $navigation = false,
	 	 bool     $multiple   = false,
	 	 bool     $eager      = false,
	 	 ?string  $through    = null
	 ){
	 	$this->through = $through;
	 	parent::__construct($pmodel, $fmodel, $field, $pk, $fk, $navigation, true, $eager);
	 } 

	 private function get_class_name(string $long_class_name){
         $nameparts = explode("\\", $long_class_name);
         return end($nameparts);
     }

	 public function get_through_model(){
	 	 $pmodelname = $this->get_class_name($this->pmodel); 
         $fmodelname = $this->get_class_name($this->fmodel); 

         if(!$this->through)
         	 throw new \Exception("No through model was defined for this many to many relationship between: {$pmodelname} and {$fmodelname}");

         $modelclass  = $this->through;
	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext();
	 	 
	 	 return [$table, $modelclass, $dbclass, strtolower($pmodelname)."_id", strtolower($fmodelname)."_id"];
	 }
}

