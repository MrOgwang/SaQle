<?php
namespace SaQle\Core\Chain;

use SaQle\Core\Chain\Base\BaseHandler;

class DefaultHandler extends BaseHandler{

     public function handle(mixed $request): mixed{
          /**
           * Do nothing
           * */
         return parent::handle($request);
     }
}

