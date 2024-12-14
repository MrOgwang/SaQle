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
 * Define a relationship between two models.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

declare(strict_types = 1);
namespace SaQle\Dao\Field\Relations\Base;

use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use Closure;

class BaseRelation implements IRelation{
	 /**
	  *  Create a one to one relation instance
	  * @param          string $pdao:  The model class name for table A, where primary key is defined.
	  * @param          string $fdao:  The model class name for table B, where foreign key is defined.
	  * @param nullable string $field: The name of the field on table A to assign the table B object to on fetch
	  * @param nullable string $pk:    The field on table A to use as primary key, if not provided defaults to the primary key defined on A
	  * @param nullable string $fk:    The field on table B to use as foreign key, if not provided defaults to the primary key defined on B
	  * @param          bool   $isnav: Whether this is a navigational field or not. 
	  *                                Navigational field values are not inserted in db. Defaults to false   
	  * @param bool     multiple:      Whether to return single object or multiple objects on fetch 
	  */
	 public function __construct(
	 	 private string                $pdao,
	 	 private string                $fdao,
	 	 private ?string               $field = null, 
	 	 private ?string               $pk = null,
	 	 private ?string               $fk = null,
	 	 private bool                  $isnav    = false,
	 	 private bool                  $multiple = false,
	 	 private bool                  $eager    = false
	 ){}

	 /**
	  * Get the primary model class
	  * */
	 public function get_pdao() : string{
	 	return $this->pdao;
	 }

	 /**
	  * Get the foreign model class
	  * */
	 public function get_fdao() : string{
	 	return $this->fdao;
	 }

	 /**
	  * Get field
	  * */
	 public function get_field() : string{
	 	return $this->field;
	 }

	 /**
	  * Get the primary key field name
	  * */
	 public function get_pk() : string{
	 	return $this->pk;
	 }

	 /**
	  * Get the foerign key field name
	  * */
	 public function get_fk() : string{
	 	return $this->fk;
	 }

	 /**
	  * Get is navigational setting
	  * */
	 public function get_isnav() : bool{
	 	return $this->isnav;
	 }

	 /**
	  * Get multiple setting
	  * */
	 public function get_multiple() : bool{
	 	return $this->multiple;
	 }

	 /**
	  * Get eager setting
	  * */
	 public function get_eager() : bool{
	 	return $this->eager;
	 }


	  /**
	  * Set the primary model class
	  * */
	 public function set_pdao(string $pdao){
	 	$this->pdao = $pdao;
	 }

	 /**
	  * Set the foreign model class
	  * */
	 public function set_fdao(string $fdao){
	 	$this->fdao = $fdao;
	 }

	 /**
	  * Set field
	  * */
	 public function set_field(string $field){
	 	$this->field = $field;
	 }

	 /**
	  * Set the primary key field name
	  * */
	 public function set_pk(string $pk){
	 	$this->pk = $pk;
	 }

	 /**
	  * Set the foerign key field name
	  * */
	 public function set_fk(string $fk){
	 	$this->fk = $fk;
	 }

	 /**
	  * Set is navigational setting
	  * */
	 public function set_isnav(bool $isnav){
	 	$this->isnav = $isnav;
	 }

	 /**
	  * Set multiple setting
	  * */
	 public function set_multiple(bool $multiple){
	 	$this->multiple = $multiple;
	 }

	 /**
	  * Set eager setting
	  * */
	 public function set_eager(bool $eager){
	 	$this->eager = $eager;
	 }
}
?>
