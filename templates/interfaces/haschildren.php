<?php
declare(strict_types = 1);
namespace SaQle\Templates\Interfaces;

interface HasChildren{
	 public function get_children() : array;
	 public function get_content_key(): string;
}
?>