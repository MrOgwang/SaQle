<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

/**
 * Will handle number, range
 * */

abstract class NumericInputControl extends InputControl{
     public mixed $min = null;
     public mixed $max = null;
     public mixed $step = null;
}
