<?php
namespace SaQle\Auth\Providers\Interfaces;

use SaQle\Auth\Providers\Interfaces\SessionProvider;

interface ProviderResolver{
     public function resolve_provider(): SessionProvider;
}
