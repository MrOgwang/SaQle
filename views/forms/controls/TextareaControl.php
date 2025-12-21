<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls;

use SaQle\Views\Forms\Controls\Base\FormControl;

class TextareaControl extends FormControl{
     protected mixed $value = null;
     protected mixed $rows = 3;
     protected mixed $cols = null;
     protected mixed $maxlength = null;
     protected mixed $minlength = null;
     protected bool $wrap = false;

     public function render(): string{
         return '<textarea '.$this->render_attributes().'>'.htmlspecialchars((string)$this->value).'</textarea>';
     }
}
