<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

/**
 * Will handle files
 * */

abstract class BinaryInputControl extends InputControl{
     public ?array $accept = null;
     public bool $multiple = false;
     public bool $capture = false;
}
