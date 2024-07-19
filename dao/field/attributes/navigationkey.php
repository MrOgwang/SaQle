<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

use Attribute;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NavigationKey implements IRelation{
	 /**
	  *  Create a new navigation key instance
	  * @param string $pdao:     The data access object class for the primary key
	  * @param string $fdao:     The data access object class for the foreign key
	  * @param bool   $multiple: Whether to return an array or single object
	  * @param string $field:    The name of the field to assign the results to
	  * @param bool   $include:  Whether to include this navigation result automatically or not.
	  * @param string $pfkeys:   The primary and foreign key to use as a connector
	  */
	 public function __construct(
	 	 private string   $pdao,
	 	 private string   $fdao, 
	 	 private bool     $multiple, 
	 	 private ?string  $field    = null, 
	 	 private bool     $include  = false,
	 	 private ?string  $pfkeys   = null
	 ){}

	 /**
	  * Get the primary data access object class
	  * */
	 public function get_pdao(){
	 	return $this->pdao;
	 }

	 /**
	  * Get the foreign data access object class
	  * */
	 public function get_fdao(){
	 	return $this->fdao;
	 }

	 /**
	  * Get multiple setting
	  * */
	 public function get_multiple(){
	 	return $this->multiple;
	 }

	 /**
	  * Get field
	  * */
	 public function get_field(){
	 	return $this->field;
	 }

	 /**
	  * Get include setting
	  * */
	 public function get_include(){
	 	return $this->include;
	 }

	 /**
	  * Get primary and foreign keys
	  * */
	 public function get_pfkeys(){
	 	return $this->pfkeys;
	 }
}
?>
