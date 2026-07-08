<?php

namespace SaQle\Routes\Canonical;

use SaQle\Http\Request\Request;

interface CanonicalUrlPolicy{
     /**
     * Returns a canonical redirect if needed, or null if request is acceptable.
     */
     public function canonicalize(Request $request): ?CanonicalRedirect;
}
