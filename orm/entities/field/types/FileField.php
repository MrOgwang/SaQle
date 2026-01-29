<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Field;

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
	  * */
	 protected string $storage; //public, private

	 //Upload path: This is where the file will be uploaded to
	 protected null|string|callable $upload_to = null;

	 //a callback to rename the file
	 protected null|callable $rename = null;

	 /**
	  * Is the file publicly acessible?
	  * 
	  * If a file is public, it will be uploaded to the public folder of the project. This means
	  * it can be downloaded or streamed without any restrictions
	  * 
	  * If a file is not public, it will be saved outside the public folder, which means
	  * there is control on how the file is accessed.
	  * */
	 protected bool $public = false;

	 //the maximum file size in bytes
	 protected mixed $max_size = null;

	 //the minimum file size in bytes
	 protected mixed $min_size = null;

	 //restrict to images only
	 protected bool $image_only = false;

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
	 	 parent::__construct(...$kwargs);
	 }
}

