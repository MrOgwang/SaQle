<?php

namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Types\{Pk, Text, Number, File};
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Field\Attributes\{TextFieldValidation, NumberFieldValidation, FileFieldValidation};
use SaQle\Controllers\Forms\FieldDataSource;

abstract class Simple{
	protected string $dtype = 'string';
	protected array $attributes = [];
	protected IValidator $validator;
	protected IControl   $control;
	public function __construct(...$kwargs){
		$this->set_validator(...$kwargs);
		$this->set_control(...$kwargs);
		$this->is_data_source(...$kwargs);
		$this->set_data_type(...$kwargs);
	}

	protected function get_validation_properties(){
		return [
			/**
			 * The data type.
			 * */
			'dtype' => 'type', 

			/**
			 * Whether to allow null values: works for text, numbers and files.
			 * */
			'null' => 'allow_null', 

			/**
			 * Whether value must be provided. works for text, numbers.
			 * If required is true, null will be false
			 * */
			'required' => 'is_required', 

			/**
			 * The maximum value allowed. 
			 * For text, this counts the number of characters.
			 * For numbers, this is the value 
			 * For files, this is the size
			 * */
			'max' => 'max',

			/**
			 * The minimum value allowed.
			 * Works as max above
			 * */
			'min' => 'min',

			/**
			 * Whether the maximum value is inclusive.
			 * */
			'imax' => 'max_inclusive',

			/**
			 * Whether the minimum value is inclusive
			 * */
			'imin' => 'min_inclusive',

			/**
			 * The number of characters for text,
			 * The number of digits for numbers,
			 * The number of characters in a file name
			 * */
			'length' => 'length',

			/**
			 * The pattern to match this value against.
			 * For files, this pattern will be matched on file name
			 * */
			'pattern' => 'pattern',

			/**
			 * The validator for a field will usually be picked from its type,
			 * but you can override this behavior by specifying a validator type.
			 * This will take the form of text, number. Note that you cannot ovveride the validator
			 * type for a file.
			 * */
			'vtype' => 'vtype'
		];
	}

	protected function get_control_properties(){
		return [
			/**
			 * The type of formcontrol
			 * */
			'ctype' => 'type',

			/**
			 * The name of form control
			 * */
	 	    'name' => 'name',

	 	    /**
	 	     * The label of the form control
	 	     * */
	 	    'label' => 'label',

	 	    /**
	 	     * Whether form control is required
	 	     * */
	 	    'required' => 'required',
            
            /**
             * Whether to disable control or not
             * */
	 	    'disabled' => 'disabled',

            /**
             * Id of form to associate with controller
             * */
	 	    'form' => 'form',

	 	    /**
	 	     * Data array to attach to the control
	 	     * */
	 	    'data' => 'data',

		];
	}

	protected function set_validator(...$kwargs){
		 $validation_properties = $this->get_validation_properties();
		 $newprops = $this->translate_properties($validation_properties, $kwargs);
		 if($this instanceof Text || (isset($kwargs['vtype']) && $kwargs['vtype'] == 'text')){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'string';
		 	}
			$this->attributes[TextFieldValidation::class] = $newprops;
		 }elseif($this instanceof Number || (isset($kwargs['vtype']) && $kwargs['vtype'] == 'number')){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'int';
		 	}
		 	$this->attributes[NumberFieldValidation::class] = $newprops;
		 }elseif($this instanceof File){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'file';
		 	}
		 	$this->attributes[FileFieldValidation::class] = $newprops;
		 }
	}

	protected function set_control(...$kwargs){

	}

	protected function is_data_source(...$kwargs){
		$is_source = $this instanceof Relation && isset($kwargs['isnav']) && $kwargs['isnav'] === false ? false : true;
		if($is_source){
			$this->attributes[FieldDataSource::class] = [];
		}
	}

	protected function translate_properties($propmap, $incoming){
		$newprops = [];
		foreach($propmap as $key => $name){
			if(array_key_exists($key, $incoming)){
				$newprops[$name] = $incoming[$key];
			}
		}
		return $newprops;
	}

	protected function set_data_type(...$kwargs){
		 /**
		 * If the type is set in the kwargs with a dtype key, return that.
		 * */
		 if(isset($kwargs['dtype'])){
		 	$this->dtype = $kwargs['dtype'];
			return;
		 }

		 /**
		 * return string for files and text, int for number
		 * */
		 if($this instanceof Text || $this instanceof File){
		 	$this->dtype = 'string';
		 	return;
		 }elseif($this instanceof Number){
		 	$this->dtype = 'int';
		 	return;
		 }

		 /**
		  * For any other instances, check if the validator type is
		  * provided and return the type associated with it.
		  * */
		 if( isset($kwargs['vtype']) && $kwargs['vtype'] == 'text' )
		 	$this->dtype = 'string';
		 	return;
		 if( isset($kwargs['vtype']) && $kwargs['vtype'] == 'number' )
		 	$this->dtype = 'int';
		 	return;

         /**
          * If all fails, return string.
          * */
		 $this->dtype = 'string';


	}

	public function get_data_type(){
		return $this->dtype;
	}

	public function get_attributes(){
		return $this->attributes;
	}
}
?>