<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

/**
 * Will handle number, range
 * */

abstract class NumericInputControl extends InputControl{
     protected mixed $min = null;
     protected mixed $max = null;
     protected mixed $step = null;
}
