<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;

class TextField extends TextualField {
	 //the minimum length allowed
	 protected string $size = 'regular'; //big, small, medium, tiny, regular

}

