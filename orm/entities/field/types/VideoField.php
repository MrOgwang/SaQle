<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;

class VideoField extends MediaField {
	 //duration in seconds
     protected ?int $min_duration = null;
     protected ?int $max_duration = null;
     protected ?int $duration = null;

	 public function __construct(...$kwargs){
	 	 $kwargs['mime_types'] = $kwargs['mime_types'] ?? ['video/*'];
	 	 parent::__construct(...$kwargs);
	 }
}

