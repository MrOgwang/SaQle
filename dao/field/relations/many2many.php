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
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Migration\Tracker\MigrationTracker;
use SaQle\Commons\FileUtils;

class Many2Many extends BaseRelation implements IRelation{
	 use FileUtils;

	 private ?string $through = null;

	 public function __construct(
	 	 string   $pdao,
	 	 string   $fdao,
	 	 ?string  $field = null, 
	 	 ?string  $pk       = null,
	 	 ?string  $fk       = null,
	 	 bool     $isnav    = false,
	 	 bool     $multiple = false,
	 	 bool     $eager    = false,
	 	 ?string  $through  = null
	 ){
	 	$this->through = $through;
	 	parent::__construct($pdao, $fdao, $field, $pk, $fk, $isnav, true, $eager);
	 } 

	 public function get_through_model_schema(){
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

         $pdao = $this->get_pdao();
	 	 $fdao = $this->get_fdao();
	 	 $pdao_state = $pdao::state();
	 	 $fdao_state = $fdao::state();

         $first_pointer = strtolower($pdao_state->get_class_name().$fdao_state->get_class_name());
         $other_pointer = strtolower($fdao_state->get_class_name().$pdao_state->get_class_name());
         $first_pointer = str_replace("schema", "", $first_pointer)."schema";
         $other_pointer = str_replace("schema", "", $other_pointer)."schema"; 

         foreach($last_throughs as $ctx => $throughs){
         	 foreach($throughs as $pointer => $schema){
         	 	if($pointer === $first_pointer || $pointer === $other_pointer){
         	 		if($pointer === $first_pointer){
         	 			$table_name = $first_pointer;
         	 		}else{
         	 			$table_name = $other_pointer;
         	 		}
         	 		return [$table_name, $schema, $ctx];
         	 	}
         	 }
         }

         throw new \Exception("No through model was defined for this many to many relationship between: {$pdao} and {$fdao}");
	 }

	 public function get_through_model(){
	 	 [$table_name, $schema, $ctx] = $this->get_through_model_schema();
	 	 $state = $schema::state();
	 	 return [$table_name, $state->get_associated_model_class(), $ctx];
	 }
}
?>
