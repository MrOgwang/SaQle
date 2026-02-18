<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class SlugField extends CharField {
	 
	 #[ShouldValidate()]
	 protected bool $slug = true;
	 
}

