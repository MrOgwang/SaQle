<?php

namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Types\{Pk, TextType, NumberType, FileField, OneToOne, OneToMany, ManyToMany, TimestampField};
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Field\Attributes\{TextFieldValidation, NumberFieldValidation, FileFieldValidation};
use SaQle\Controllers\Forms\FieldDataSource;
use SaQle\Dao\Field\Controls\FormControl;

abstract class Simple{
	/**
	 * The name of the property this field is assigned to on the model.
	 * */
	protected string $property_name = '';

	/**
	 * The model class name where field is defined. 
	 * */
	protected string $model_class;

	/**
	 * The pk name of the model class where field is defined.
	 * */
	protected string $model_class_pk;

	/**
	 * All the validation, custom, source and form attributes/
	 * */
	protected array $attributes = [];

	/**
	 * Keep the array of key word arguments passed in field contsructor
	 * */
	protected array $kwargs;

	/**
	 * The form control associated with this field. This is used in generic forms.
	 * */
	protected IControl   $control;
	
	public function __construct(...$kwargs){
		/**
		 * 1. There are four types that may come in through the kwargs:
		 * - dtype: is the database type for field: e.g INT, VARCHAR
		 * - vtype: is the validation type for field. e.g text or number
		 * - ptype: is the primitive type for the value that will be stored in field. e.g int, float, string, double etc
		 * - ctype: is the type of input control to be used on forms for the field.
		 * 
		 * 2. There are two types of names that may come in through the kwargs
		 * - cname: this is the name used on the form control for name and id attributes of input tag
		 * - dname: this is the name to be used as the column name in the database.
		 * */
		$this->kwargs = $kwargs;
	}

	//setters
	public function set_property_name(string $name){
		$this->property_name = $name;
	}

	public function set_model_class(string $class){
		$this->model_class = $class;
	}

	public function set_model_class_pk(string $pk){
		$this->model_class_pk = $pk;
	}

	protected function set_control_attributes(...$kwargs){
		 $control_properties = $this->get_control_properties();
		 $newprops = $this->translate_properties($control_properties, $kwargs);
		 $this->attributes[FormControl::class] = $newprops;
	}

	public function add_kwargs($name, $val){
		$this->kwargs[$name] = $val;
	}

	protected function set_validator_attributes(...$kwargs){
		 $validation_properties = $this->get_validation_properties();
		 $newprops = $this->translate_properties($validation_properties, $kwargs);
		 if(isset($newprops['choices']) && isset($kwargs['usekeys'])){
		 	 $newprops['choices'] = array_keys($newprops['choices']);
		 }
		 if($this instanceof TextType || (isset($kwargs['vtype']) && $kwargs['vtype'] == 'text')){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'string';
		 	}
			$this->attributes[TextFieldValidation::class] = $newprops;
		 }elseif($this instanceof NumberType || (isset($kwargs['vtype']) && $kwargs['vtype'] == 'number')){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'int';
		 	}
		 	$this->attributes[NumberFieldValidation::class] = $newprops;
		 }elseif($this instanceof FileField){
		 	if(!array_key_exists('type', $newprops)){
		 		$newprops['type'] = 'file';
		 	}
		 	$this->attributes[FileFieldValidation::class] = $newprops;
		 }
	}

	protected function set_source_attributes(...$kwargs){
		$is_source = $this instanceof Relation && isset($this->kwargs['isnav']) && $this->kwargs['isnav'] === true ? false : true;
		if($is_source){
			 $this->attributes[FieldDataSource::class] = [];
		}
	}

	public function initialize(){
		 #initialize database properties.
		 /**
		  * If the dname is not provided, make the current property_name the dname.
		  * */
		 $this->kwargs['dname'] = $this->kwargs['dname'] ?? $this->property_name;

		 #Initialize control properties.
		 /**
		  * If the cname is not provided, make it the current property name
		  * */
		 $this->kwargs['cname'] = $this->kwargs['cname'] ?? $this->property_name;
		 /**
		  * If the required attribute is not set, set required to false.
		  * */
		 $this->kwargs['required'] = isset($this->kwargs['required']) ? $this->kwargs['required'] : false;
		 /**
		  * If the label is not set, turn the current property name into a label.
		  * */
		 $this->kwargs['label'] = isset($this->kwargs['label']) ? $this->kwargs['label'] : 
		 ucwords(str_replace("_", " ", $this->property_name));

		 #Initialize validation properties.
		 /**
		  * If imax is not provided, set it to true.
		  * */
		 $this->kwargs['imax'] = isset($this->kwargs['imax']) ? $this->kwargs['imax'] : true;
		 /**
		  * If imin is not provided, set it to true.
		  * */
		 $this->kwargs['imin'] = isset($this->kwargs['imin']) ? $this->kwargs['imin'] : true;

		 $this->set_validator_attributes(...$this->kwargs);
		 $this->set_control_attributes(...$this->kwargs);
		 $this->set_source_attributes(...$this->kwargs);
	}

    //getters
    public function get_kwargs(){
    	return $this->kwargs;
    }

    public function is_required(){
    	return $this->kwargs['required'] ?? false;
    }

	public function get_model_class(){
		return $this->model_class;
	}

	public function get_model_class_pk(){
		return $this->model_class_pk;
	}

	public function get_primitive_type(){
		return $this->kwargs['ptype'];
	}

	public function get_db_column_name(){
		return $this->kwargs['dname'];
	}

	public function get_control_attributes(){
		return $this->attributes[FormControl::class] ?? [];
	}

	public function get_source_attributes(){
		return $this->attributes[FieldDataSource::class] ?? [];
	}

	public function get_attributes(){
		return $this->attributes;
	}

	protected function get_validation_properties(){
		return [
			/**
			 * The data type.
			 * */
			'ptype' => 'type', 

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
			 * The name of form control. 
			 * This will be used for the name and id attributes of the form input element constructed
			 * */
	 	    'cname' => 'name',

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

	 	    /**
	 	     * Whether control should allow multiple values
	 	     * */
	 	    'multiple' => 'multiple'
		];
	}

	protected function get_db_properties(){
		return [
			/**
			 * The name to assign to the database column.
			 * */
	 	    'dname' => 'dname',

	 	    /**
			 * The database column type.
			 * Exampl: VARCHAR, BIGINT, DATETIME etc
			 * */
			'dtype' => 'dtype',

	 	    /**
	 	     * The value assigned to field.
	 	     * */
	 	    'value' => 'value',
		];
	}

	//utils
	protected function translate_properties($propmap, $incoming){
		$newprops = [];
		if(array_key_exists("reverse", $incoming)){
			foreach($propmap as $key => $name){
				if(array_key_exists($name, $incoming)){
					$newprops[$name] = $incoming[$name];
				}
			}
		}else{
			foreach($propmap as $key => $name){
				if(array_key_exists($key, $incoming)){
					$newprops[$name] = $incoming[$key];
				}
			}
		}
		return $newprops;
	}

	public function get_validation_configurations(){
		 if($this instanceof TextType){
		 	 return $this->attributes[TextFieldValidation::class] ?? [];
		 }

		 if($this instanceof NumberType){
		 	 return $this->attributes[NumberFieldValidation::class] ?? [];
		 }

		 if($this instanceof FileField){
		 	 return $this->attributes[FileFieldValidation::class] ?? [];
		 }
         
         /**
          * Primary keys and Relation keys will be by passed for now.
          * */
		 return [];
	}

	public function __toString(){
		 if(isset($this->kwargs['value']))
		 	return (string)$this->kwargs['value'];

		 return "";
	}

	public function get_field_definition(){
		 $is_field = $this instanceof Relation && isset($this->kwargs['isnav']) && $this->kwargs['isnav'] === true ? false : true;
		 if(!$is_field){
			 return null;
		 }

		 $def   = [$this->kwargs['dname']];
		 $def[] = $this->kwargs['dtype'] === "VARCHAR" ? $this->kwargs['dtype']."(".$this->kwargs['length'].")" : $this->kwargs['dtype'];
		 if($this instanceof PK){
		 	$def[] = $this->kwargs['dtype'] === "VARCHAR" ? "PRIMARY KEY" : "AUTO_INCREMENT PRIMARY KEY";
		 }
		 $def[] = $this->kwargs['required'] ? "NOT NULL" : "NULL";
		 if($this instanceof TimestampField){
		     $def[] = $this->kwargs['db_auto_init'] ? "DEFAULT CURRENT_TIMESTAMP" : "";
		     $def[] = $this->kwargs['db_auto_update'] ? "ON UPDATE CURRENT_TIMESTAMP" : "";
		 }else{
		 	 $def[] = isset($this->kwargs['value']) ? 'DEFAULT '.$this->kwargs['value'] : '';
		 }
 	 	 return implode(" ", $def);
	}

    /**
     * Set the raw value for this field
     * */
	public function value(mixed $value){
		 $this->kwargs['value'] = $value;
	}

	/**
	 * Get the raw value for this field
	 * */
	public function get_value(){
		return $this->kwargs['value'] ?? "";
	}
}
?>