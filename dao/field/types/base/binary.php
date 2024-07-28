<?php
namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Interfaces\ICustom;
use SaQle\Dao\Field\Attributes\FileConfig;
use SaQle\Dao\Field\FormControlTypes;

abstract class Binary extends Simple{
	protected ICustom $custom;
	public function __construct(...$kwargs){
		/**
		 * Fill in the data types.
		 * */
		$kwargs['dtype'] = "VARCHAR";
		$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::FILE->value;
		$kwargs['ptype'] = "file";
		
		/**
		 * Fill in the validation props
		 * */
		$kwargs['length'] = 255;
		$kwargs['max'] = 255;
		parent::__construct(...$kwargs);
	}

	protected function get_validation_properties(){
		return array_merge(parent::get_validation_properties(), [
			/**
			 * An array of file extensions considered valid
			 * */
			'accept' => 'accept'
		]);
	}

	protected function get_custom_properties(){
		return [
			/**
			 * Callback that returns path to save file
			 * */
			'path' => 'path', 

			/**
			 * Callback that renames uploaded files
			 * */
			'rename' => 'rename_callback',

			/**
			 * Callback that returns a path for a default file incase this value is missing.
			 * */
			'dpath' => 'default',

			/**
			 * An array of integer values to be used to crop file(images and videos)
			 * */
			'crop' => 'crop_dimensions',

			/**
			 * An array of integer values to be used to resize files(images and videos)
			 * */
			'resize' => 'resize_dimensions',

			/**
			 * A callback that returns the file to show.
			 * */
			'show' => 'show_file'
		];
	}

	protected function get_control_properties(){
		return array_merge(parent::get_control_properties(), [
			/**
			 * An array of file extensions considered valid
			 * */
			'accept' => 'accept'
		]);
	}

	protected function set_custom(...$kwargs){
		 $custom_properties = $this->get_custom_properties();
		 $newprops = $this->translate_properties($custom_properties, $kwargs);
		 $this->attributes[FileConfig::class] = $newprops;
	}

	public function get_field_attributes(){
		return $this->attributes[FileConfig::class] ?? [];
	}

	public function initialize(){
		 parent::initialize();
		 $this->set_custom(...$this->kwargs);
	}
}
?>