<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

/**
 * Will handle radio and checkboxes
 * */

abstract class ChoiceInputControl extends InputControl{
     protected bool $checked = false;
}
