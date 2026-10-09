<?php

use SaQle\Core\Components\Renderer;

if(!function_exists('__renderer')){
     function __renderer(){
         return new Renderer(request());
     }
}