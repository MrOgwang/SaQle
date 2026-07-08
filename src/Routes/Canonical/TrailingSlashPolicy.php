<?php

namespace SaQle\Routes\Canonical;

use SaQle\Http\Request\Request;

final class TrailingSlashPolicy implements CanonicalUrlPolicy{
     public function canonicalize(Request $request): ?CanonicalRedirect {
         $path = $request->uri();

         if($path !== '/' && str_ends_with($path, '/')){
             return new CanonicalRedirect(
                 rtrim($path, '/'),
                 308
             );
         }

         return null;
     }
}
