<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls;

use SaQle\Core\Forms\Controls\Base\TemporalInputControl;

class DateTimeInputControl extends TemporalInputControl{
     public string $type = 'datetime-local';
}
