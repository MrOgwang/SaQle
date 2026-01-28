<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Core\Support\CharSet;

class TextualField extends Field {
	//the minimum length allowed
	 protected ?int $min_length = null;

	 //the maximum length allowed
	 protected ?int $max_length = null;

	 //the exact length allowed
	 protected ?int $length = null;

	 //the regex pattern to match
	 protected ?string $pattern = null;

	 //the field character set
	 protected ?CharSet $charset = null;

	 //whether to allow blank or not
	 protected bool $blank = true;
}

