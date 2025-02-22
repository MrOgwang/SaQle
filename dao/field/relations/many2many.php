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
namespace SaQle\Dao\Field\Relations;

use SaQle\Dao\Field\Relations\Base\BaseRelation;
use SaQle\Migration\Tracker\MigrationTracker;
use SaQle\Commons\FileUtils;

class Many2Many extends BaseRelation{
	 use FileUtils;

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
	 	 if($this->through)
	 		 return $this->through;

	 	 /**
	 	 * Now go through the complicated process of finding a through model.
	 	 * */
	 	 $trackerfile = DOCUMENT_ROOT."/migrations/migrationstracker.bin";
         $tracker = $this->unserialize_from_file($trackerfile);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }
         $last_throughs = $tracker->get_through_models();
         $pmodel_name = $this->get_class_name($this->pmodel); 
         $fmodel_name = $this->get_class_name($this->fmodel); 

         $first_pointer = strtolower($pmodel_name.$fmodel_name);
         $other_pointer = strtolower($fmodel_name.$pmodel_name);

         foreach($last_throughs as $ctx => $throughs){
         	 foreach($throughs as $pointer => $model){
         	 	if($pointer === $first_pointer || $pointer === $other_pointer){
         	 		if($pointer === $first_pointer){
         	 			$table_name = $first_pointer;
         	 		}else{
         	 			$table_name = $other_pointer;
         	 		}
         	 		return [$table_name, $model, $ctx, strtolower($pmodel_name), strtolower($fmodel_name)];
         	 	}
         	 }
         }

         throw new \Exception("No through model was defined for this many to many relationship between: {$pmodel_name} and {$fmodel_name}");
	 }
}
?>
