<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls;

use SaQle\Views\Forms\Controls\Base\TemporalInputControl;

class DateTimeInputControl extends TemporalInputControl{
     protected string $type = 'datetime-local';
}
