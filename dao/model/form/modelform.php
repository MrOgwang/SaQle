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
 * A model form will be used to generate a form when adding new model entries
 * and to auto save that data to the database.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Dao\Model\Form;

abstract class ModelForm{
     /**
      * The model to associate this form with. 
      * If the model is not provided, it will be deduced from the name of the form.
      * 
      * @var string
      * */
	 public $model = "";

	 /**
	  * The fields of the model whose values will come from form data
	  * 
	  * @var array
	  * */
	 public $from_form = [];

	 /**
	  * The fields whose values are pre defined. These values are saved like this 
	  * when a new record is created from this model.
	  * 
	  * This is a key => value array where key is the field name and value is the value to assign
	  * to field or a callback to excecute to get the value.
	  * 
	  * @var array
	  * */
	public $pre_defined = [];

	/**
	 * If a field value is designated to come from form data but the field is a foreign key field
	 * or a navigational field add it to the create_with_new to create a new record
	 * 
	 * @var array
	 * */
	public $create_with_new = [];

	/**
	 * If a field value is designated to come from form data but the field is a foreign key field
	 * or a navigational field add it to the create_with_existing to pull from existing records.
	 * 
	 * @var array
	 * */
	public $create_with_existing = [];

	/**
	 * If a field value is designated to come from form data but the field is a foreign key field
	 * or a navigational field add it to the edit_with_new to create a new record when editing parent record
	 * 
	 * @var array
	 * */
	public $edit_with_new = [];

	/**
	 * If a field value is designated to come from form data but the field is a foreign key field
	 * or a navigational field add it to the create_with_existing to pull from existing records when
	 * editing parent record
	 * 
	 * @var array
	 * */
	public $edit_with_existing = [];

	/**
	 * This list tells the edit controler which fields are optional to update
	 * */
	public $optional_update = [];

}
?>