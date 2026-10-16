<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\ServerException;
use SaQle\Http\HttpStatus;

class InternalServerErrorException extends ServerException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, $data);
     }
}
