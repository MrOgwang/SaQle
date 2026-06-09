<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Traits\HasChoices;

class IntegerChoiceField extends IntegerField {
     use HasChoices;
}