<?php
namespace SaQle\Auth\Interfaces;

interface IdentityProviderResolverInterface {
     public function resolve(): IdentityProviderInterface;
}
