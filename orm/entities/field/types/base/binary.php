<?php
namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Assert\Assert;

abstract class Binary extends RealField{
	 /**
	  * This is a virtual property to enable the client get the full disk path
	  * of a file or an array of disk paths depending on whether the multiple setting is on or off.
	  * */
	 public mixed $file_path {
	 	 get {
	 	 	 $field_name = $this->field_name;
	 	 	 $data       = !is_null($this->context) ? (object)$this->context : null;
	 	 	 $file_value = $data->$field_name ?? null;
	 	 	 if(!$file_value)
	 	 	 	 return $this->multiple ? [] : null;

	 	 	 $path  = $this->path($data);
	 	     $files = explode("~", $file_value);
	 	     $paths = [];
	 	     foreach($files as $file_name){
		 	     $paths[] = $path.$file_name;
		     }
	 	     return $this->multiple ? $paths : $paths[0];
	 	 }
	 }

	 /**
	  * This is a virtual property to enable the client get the name
	  * of a file or an array of names depending on whether the multiple setting is on or off.
	  * */
	 public mixed $file_name {
	 	 get {
	 	 	 $field_name = $this->field_name;
	 	 	 $data       = !is_null($this->context) ? (object)$this->context : null;
	 	 	 $file_value = $data->$field_name ?? null;
	 	 	 if(!$file_value)
	 	 	 	 return $this->multiple ? [] : null;

	 	     $files = explode("~", $file_value);
	 	     return $this->multiple ? $files : $files[0];
	 	 }
	 }

	 /**
	  * Whether to allow multiple files for this field or not
	  * */
	 public protected(set) bool $multiple = true {
	 	 set(bool $value){
	 	 	 $this->multiple = $value;
	 	 }

	 	 get => $this->multiple;
	 }

	 /**
	  * During file upload for images and videos, give an array of integer sizes
	  * for cropping
	  * */
	 public protected(set) array $crop_dimensions = [] {
	 	 set(array $value){
	 	 	 Assert::allPositiveInteger($value, 'All crop dimensions must be integers greater than 0');
	 	 	 $this->crop_dimensions = $value;
	 	 }

	 	 get => $this->crop_dimensions;
	 }

     /**
	  * During file upload for images and videos, give an array of integer sizes
	  * for resizing
	  * */
	 public protected(set) array $resize_dimensions = [] {
	 	 set(array $value){
	 	 	 Assert::allPositiveInteger($value, 'All resize dimensions must be integers greater than 0');
	 	 	 $this->resize_dimensions = $value;
	 	 }

	 	 get => $this->resize_dimensions;
	 }

	 /**
	  * These fields must be included in the select staement because they are needed 
	  * for path, rename, url and default_url callbacks
	  * */
	 public protected(set) array $required_fields = [] {
	 	 set(array $value){
	 	 	 Assert::allString($value, 'All required fields must be strings');
	 	 	 $this->required_fields = array_unique($value);
	 	 }

	 	 get => $this->required_fields;
	 }

	 /**
	  * An array of file types to accept
	  * */
	 public protected(set) ?array $accept = null {
	 	 set(?array $value){
	 	 	 Assert::allString($value, 'All required fields must be strings');
	 	 	 $this->accept = array_unique($value);
	 	 }

	 	 get => $this->accept;
	 }

     /**
      * Get the directory where uploaded file(s) are saved inside the root media folder
      * 
      * @param mixed $model - this is the current object to be saved or updated
      * */
	 public function path(mixed $model) : string{
	 	 return "";
	 }

     /**
      * Call this function to rename the files before they are saved
      * 
      * @param mixed  $model      - this is the current object to be saved or updated
      * @param string $file_name  - this is the uploaded file name
      * @param int    $file_index - if multiple files are uploaded, this is the zero based index of the file
      * */
	 public function rename(mixed $model, string $file_name, int $file_index = 0) : string{
	 	 return $file_name;
	 }

     /**
      * Where there were no files uploaded, but there is a default file that can be shown, return it with default url
      * */
	 public function default_url(mixed $model) : string | array{
	 	 return "";
	 }

     //Create a new binary field object
	 public function __construct(...$kwargs){
		 $kwargs['column_type']     = "VARCHAR";
		 $kwargs['primitive_type']  = "file";
		 $kwargs['validation_type'] = "text";
		 $kwargs['length']          = 255;
		 $kwargs['maximum']         = 255;
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['accept']);
	 }
}
?>