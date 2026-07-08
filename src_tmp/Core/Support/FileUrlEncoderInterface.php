<?php

namespace SaQle\Core\Support;

interface FileUrlEncoderInterface {

     public function encode(array $file_meta) : string;

     public function decode(string $encoded) : array;

}