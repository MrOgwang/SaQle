<?php
namespace SaQle\Dao\Field\Types\Base;

abstract class Scalar extends Simple{
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected function get_validation_properties(){
		return array_merge(parent::get_validation_properties(), [
			/**
			 * An array of choices from which the value must exists.
			 * */
			'choices' => 'choices',
		]);
	}

	protected function get_control_properties(){
		return array_merge(parent::get_control_properties(), [
			/**
			 * An array of choices from which the value can be picked
			 * */
			'choices' => 'choices',

			/**
			 * Given a set of choices, whether to allow multiple values or just one
			 * */
			'multiple' => 'multiple',

			/**
              * Whether control is readonly or not
              * */
	 	    'readonly' => 'readonly',

            /**
             * Step value for number input types
             * */
	 	    'step' => 'step',

            /**
             * Controller placeholder
             * */
	 	    'placeholder' => 'placeholder',

	 	    /**
             * Array of options for select box, radio buttons or checkboxes
             * */
	 	    'choices' => 'options',

             /**
             * The default value to display on the control
             * */
	 	    'default' => 'default',
		]);
	}

	protected function get_field_properties(){
		return array_merge(parent::get_field_properties(), [
			/**
             * The default value to use if a value is not provided.
             * */
	 	    'default' => 'default',
		]);
	}
}
?>