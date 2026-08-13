<?php

namespace SaQle\Lib\Components\FormField;

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\Request;

final class Definition extends ComponentDefinition {

     public function dependencies() : array {
         return [
             'scripts' => [
                 '@saqle.lib.autoform'
             ],
             'styles' => [
                 '@saqle.lib.autoform'
             ],
         ];
     }
}