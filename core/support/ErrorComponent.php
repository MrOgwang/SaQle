<?php

namespace SaQle\Core\Support;

use SaQle\Http\Response\Message;

interface ErrorComponent {
	 public function get(int $code, string $message, mixed $data) : Message;
}