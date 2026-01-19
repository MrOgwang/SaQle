<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

/**
 * Will handle date, time, datetime-local, month, week
 * */

abstract class TemporalInputControl extends InputControl{
     public mixed $min = null;
     public mixed $max = null;
     public mixed $step = null;
}
