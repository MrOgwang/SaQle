<?php

namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Interfaces\IValidator;

abstract class Simple{
	protected IValidator $validator;
	protected IControl   $control;
	public function __construct(...$kwargs){

	}

	protected function get_validation_properties(){
		return [
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
		];
	}

	protected function get_control_properties(){
		return [
			/**
			 * The type of formcontrol
			 * */
			'type' => 'type',

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
}
?>