<?php
namespace SaQle\Auth\Interfaces;

interface ContactRepositoryInterface {
     public function exists(string $contact, ?string $type = null, ?string $owner_type = null): bool;
}