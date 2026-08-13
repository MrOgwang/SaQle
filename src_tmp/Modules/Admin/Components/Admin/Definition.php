<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\Request;

final class Definition extends ComponentDefinition {

     public function dependencies() : array {
         return [
             'scripts' => [
                 '~https://unpkg.com/lucide@latest'
             ],
         ];
     }
}