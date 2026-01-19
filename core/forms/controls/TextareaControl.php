<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls;

use SaQle\Core\Forms\Controls\Base\FormControl;

class TextareaControl extends FormControl{
     public mixed $value = null;
     public mixed $rows = 3;
     public mixed $cols = null;
     public mixed $maxlength = null;
     public mixed $minlength = null;
     public bool $wrap = false;

     public function render(): string{
         return '<textarea '.$this->render_attributes().'>'.htmlspecialchars((string)$this->value).'</textarea>';
     }
}
