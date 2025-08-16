<?php
namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Assert\Assert;
use SaQle\Commons\{UrlUtils, StringUtils};
use SaQle\DirManager\DirManager;

abstract class Binary extends RealField{
	 use UrlUtils, StringUtils;

	 /**
	  * This is a virtual property to enable the client get the full disk path
	  * of a file or an array of disk paths depending on whether the multiple setting is on or off.
	  * */
	 public mixed $file_path {
	 	 get {
	 	 	 if(!$this->value)
	 	 	 	 return $this->multiple ? [] : null;

	 	 	 $path  = $this->path($this->context);
	 	     $files = explode("~", $this->value);
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
	 	 	 if(!$this->value)
	 	 	 	 return $this->multiple ? [] : null;

	 	     $files = explode("~", $this->value);
	 	     return $this->multiple ? $files : $files[0];
	 	 }
	 }

	 /**
	  * Whether to allow multiple files for this field or not
	  * */
	 public protected(set) bool $multiple = false {
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
	  * These fields must be included in the select statement because they are needed 
	  * for path, rename and default_path callbacks
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
      * All files will be saved to the media root folder as specified with MEDIA_FOLDER setting by default
      * 
      * Override to provide custom path
      * 
      * @param array $context - this is the current data to be saved or updated
      * */
	 public function path(array $context) : string {
	 	 return new DirManager()->create_dir();
	 }

     /**
      * Call this function to rename the files before they are saved
      * 
      * @param array  $context    - this is the current data to be saved or updated
      * @param string $file_name  - this is the uploaded file name
      * @param int    $file_index - if multiple files are uploaded, this is the zero based index of the file
      * */
	 public function rename(array $context, string $file_name, int $file_index = 0) : string {
	 	 return $file_name;
	 }

     /**
      * Where there were no files uploaded, but there is a default file that can be shown, return its path
      * 
      * This is particularly useful for images and videos
      * 
      * Override with own implementation to return a default path
      * */
	 public function default_file_path(array $context) : string | array {
	 	 return "";
	 }

	 private function is_url(string $value){
	 	 return filter_var($value, FILTER_VALIDATE_URL) !== false;
	 }

     /**
      * Return the url/urls of a file/files
      * */
	 public function render() : mixed {
         //A raw file, file that has already been processed by render or a valid url
	 	 if(is_array($this->value) || (is_string($this->value) && $this->is_url((string)$this->value))) 
	 	 	 return $this->value;

         $path  = $this->path($this->context);
         $files = is_string($this->value) && !empty(trim($this->value)) ? explode("~", $this->value) : [];
	 	 if(is_null($this->value) || empty(trim($this->value))){
	 	 	 $default_path = $this->default_file_path($this->context);
	 	 	 if(empty(trim($default_path)))
	 	 	 	 return '';

	 	 	 $path = dirname($default_path);
	 	 	 $files = [basename($default_path)];
	 	 }

         $file_data = [];
         $urls = [];
         $sizes = $this->resize_dimensions ? $this->resize_dimensions : ($this->crop_dimensions ? $this->crop_dimensions : []);
         $prefer = $this->resize_dimensions ? 'resized' : ($this->crop_dimensions ? 'cropped' : 'original');
         if($sizes){
         	 $file_data['size'] = max($sizes);
         }
         $file_data['prefer'] = $prefer;
         //include required fields
     	 foreach($this->required_fields as $rf){
     	 	 if(isset($this->context[$rf])){
     	 	 	 $file_data[$rf] = $this->context[$rf];
     	 	 }
     	 }
     	 //include model info
     	 $file_data = array_merge($file_data, $this->model_info);

         foreach($files as $file_name){
         	 $file_data['path'] = $path;
         	 $file_data['file_name'] = $file_name;
         	 $url_token = $this->encrypt(json_encode($file_data), MEDIA_KEY, 'media-url-salt');
	 		 $urls[] = $this->add_url_parameter(rtrim(ROOT_DOMAIN, '/').MEDIA_URL, 'token', $url_token);
	 	 }

	 	 return $this->multiple ? $urls : $urls[0];
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
