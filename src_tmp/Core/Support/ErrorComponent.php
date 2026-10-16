<?php

namespace SaQle\Core\Support;

use SaQle\Http\Message\Message;

interface ErrorComponent {
	 public function get(int $code, string $message, mixed $data) : Message;
}