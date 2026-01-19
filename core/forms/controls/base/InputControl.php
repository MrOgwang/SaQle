<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

abstract class InputControl extends FormControl{
      public string $type;
      public mixed $value = null;

      public function render(): string {
           return '<input type="'.$this->type.'" '.$this->render_attributes().' />';
      }
}
