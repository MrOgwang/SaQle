<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

/**
 * Will handle date, time, datetime-local, month, week
 * */

abstract class TemporalInputControl extends InputControl{
     protected mixed $min = null;
     protected mixed $max = null;
     protected mixed $step = null;
}
