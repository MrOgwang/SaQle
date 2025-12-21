<?php

namespace SaQle\Views\Forms\Fields;

use SaQle\Views\Forms\Controls\Base\FormControl;

class FormField {
      protected FormControl $control;
      protected ?string     $label = null;
      protected ?string     $helper_text = null;
      protected array       $errors = [];
      protected array       $attributes = []; // wrapper attrs
      protected string      $template;
}
