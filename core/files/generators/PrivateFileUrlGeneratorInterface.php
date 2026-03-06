<?php

namespace SaQle\Core\Files\Generators;

interface PrivateFileUrlGeneratorInterface {
     public function generate(array $file_meta): string;
}