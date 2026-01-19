<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

/**
 * Will handle text, email, password, url, tel, search
 * */
abstract class TextualInputControl extends InputControl{
      public ?string $placeholder = null;
      public mixed $maxlength = null;
      public mixed $minlength = null;
      public ?string $pattern = null;
      public bool $autocomplete = false;
      public ?string $inputmode = null;
      public bool $spellcheck = false;
}
