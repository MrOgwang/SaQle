<?php
namespace SaQle\Security\Models;

use SaQle\Security\Builders\ValidatorBuilder;

class FieldValidator{
	public static function validate(string $field, array $config, $value){
		/**
		 * If is_required key has not been explicitly set, or it has been assigned false
		 * */
		if(!array_key_exists("is_required", $config) || !$config['is_required']){
			 $config['allow_null']  = true;
			 $config['allow_empty'] = true;
		}else{
			$config['allow_null']  = false;
			$config['allow_empty'] = false;
		}

		/**
		 * Add a compact key with a default value of false incase input is an array
		 * */
		if(!array_key_exists("compact", $config)){
			$config['compact'] = false;
		}

		/**
		 * the allow_null and allow_empty validation must be done first before all the rest, in that order.
		 * */
		$desired_order = ['allow_null', 'allow_empty'];
		$config        = array_replace(array_flip($desired_order), $config);

		$builder = new ValidatorBuilder();
		foreach($config as $key => $val){
			$builder = self::compose_builder($key, $builder);
		}

		/**
		 * Add the general_type and field_name keys to the config
		 * */
		$config['general_type'] = self::get_general_type($config['type']);
		$config['field_name']   = $field;
		return $builder->build()->validate(input: $value, config: $config, code: 0);
	}

	private static function compose_builder(string $name, ValidatorBuilder $builder) : ValidatorBuilder{
		return match($name){
			'accept', 'type' => $builder->type(),
			'allow_null'     => $builder->null(),
			'choices'        => $builder->choices(),
			'allow_empty'    => $builder->empty(),
			'length'         => $builder->length(),
			'max'            => $builder->max(),
			'min'            => $builder->min(),
			'pattern'        => $builder->pattern(),
			'is_strict'      => $builder->strict(),
			'allow_zero'     => $builder->zero(),
			'is_absolute'    => $builder->absolute(),
			default          => $builder
		};
	}

	private static function get_general_type(string $type){
		 return match(strtolower($type)){
		 	 "tinyint", "smallint", "int", "mediumint", "bigint", "float", "double", "number" => "number",
		 	 "char", "varchar", "tinytext", "text", "mediumtext", "longtext", "string", "email", "phone", "url", "date", "time", "datetime" => "text",
		 	 "file" => "upload"
		 };
	}
}
?>