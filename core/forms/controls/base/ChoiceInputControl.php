<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

/**
 * Will handle radio and checkboxes
 * */

abstract class ChoiceInputControl extends InputControl{
     public bool $checked = false;
}
