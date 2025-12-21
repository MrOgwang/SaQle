<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

abstract class InputControl extends FormControl{
      protected string $type;
      protected mixed $value = null;

      public function render(): string {
           return '<input type="'.$this->type.'" '.$this->render_attributes().' />';
      }
}
