<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Field;
use SaQle\Orm\Database\ColumnType;
use Closure;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class FileField extends Field {
	 /**
	  * The storage backend: A storage is a named policy that uses a driver to store bytes somewhere.
	  * 
	  * Example storage configuration:
	  * 
	  * 'storages' => [
	  *     'public' => [
	  *         'driver' => 'local',
	  *         'root' => '/var/www/app/public/uploads',
	  *         'visibility' => 'public',
	  *         'base_url' => 'https://example.com/uploads',
	  *      ],
	  * 
	  *     'private' => [
	  *         'driver' => 's3',
	  *         'bucket' => 'secure-files',
	  *         'visibility' => 'private',
	  *      ],
	  * ]
	  * 
	  * Is the file publicly acessible?
	  * 
	  * If a file is public, it will be uploaded to the public folder of the project. This means
	  * it can be downloaded or streamed without any restrictions
	  * 
	  * If a file is not public, it will be saved outside the public folder, which means
	  * there is control on how the file is accessed.
	  * */
	 protected string $storage; //public, private

	 //Upload path: This is where the file will be uploaded to
	 protected null|string|array|Closure $upload_to = null;

	 //a callback to rename the file
	 protected null|array|Closure $rename_callback = null;

	 //these fields are required to properly setup path and rename
	 protected ?array $depends_on = null;

	 //the maximum file size in bytes
	 protected mixed $max_size = null;

	 //the minimum file size in bytes
	 protected mixed $min_size = null;

	 //whether to upload multiple files or not
	 protected bool $multiple = false;

	 /**
	  * A list of allowed file extensions without the dot
	  * 
	  * example: ['jpg', 'png', 'pdf']
	  * */
	 protected ?array $extensions = null;

	 /**
	  * A list of allowed mime types
	  * 
	  * example: ['image/jpeg', 'image/png', 'application/pdf', 'image/*']
	  * */
	 protected ?array $mime_types = null;

	 public function __construct(...$kwargs){
	 	 $kwargs['type'] = $kwargs['type'] ?? ColumnType::CHAR;
	 	 parent::__construct(...$kwargs);
	 }

	 public function depends_on(array $fields){
	 	 $this->depends_on = $fields;
	 	 return $this;
	 }

	 public function get_depends_on(){
	 	 return $this->depends_on;
	 }

	 public function upload_to(callable|string $upload_to){
	 	 $this->upload_to = $upload_to;
	 	 return $this;
	 }

	 public function get_upload_to(){
	 	 return $this->upload_to;
	 }

	 public function rename_callback(callable|string $rename_callback){
	 	 $this->rename_callback = $rename_callback;
	 	 return $this;
	 }

	 public function get_rename_callback(){
	 	 return $this->rename_callback;
	 }

	 public function max_size(mixed $size){
	 	 $this->max_size = $size;
	 	 return $this;
	 }

	 public function min_size(mixed $size){
	 	 $this->min_size = $size;
	 	 return $this;
	 }

	 public function get_max_size(){
	 	 return $this->max_size;
	 }

	 public function get_min_size(){
	 	 return $this->min_size;
	 }

	 public function mime_types(array $mime_types){
	 	 $this->mime_types = $mime_types;
	 	 return $this;
	 }

	 public function get_mime_types(){
	 	 return $this->mime_types;
	 }

	 public function multiple(bool $multiple = true){
	 	 $this->multiple = $multiple;
	 	 return $this;
	 }

	 public function get_multiple(){
	 	 return $this->multiple;
	 }

	 public function extensions(array $extensions){
	 	 $this->extensions = $extensions;
	 	 return $this;
	 }

	 public function get_extensions(){
	 	 return $this->extensions;
	 }
}

