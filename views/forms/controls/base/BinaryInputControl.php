<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

/**
 * Will handle files
 * */

abstract class BinaryInputControl extends InputControl{
     protected ?array $accept = null;
     protected bool $multiple = false;
     protected bool $capture = false;
}
