<?php

namespace SaQle\Http\Request;

class ErrorContext {
      public bool  $should_redirect = false;
      public bool  $should_flash_input = false;
      public bool  $should_flash_errors = false;
      public mixed $errors_payload;
      public mixed $input_payload;
}