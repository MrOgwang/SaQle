<?php
declare(strict_types = 1);

namespace SaQle\Views\Forms\Controls\Base;

/**
 * Will handle text, email, password, url, tel, search
 * */
abstract class TextualInputControl extends InputControl{
      protected ?string $placeholder = null;
      protected mixed $maxlength = null;
      protected mixed $minlength = null;
      protected ?string $pattern = null;
      protected bool $autocomplete = false;
      protected ?string $inputmode = null;
      protected bool $spellcheck = false;
}
