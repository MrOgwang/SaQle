<?php
namespace SaQle\Auth\Interfaces;

interface VerificationCodeRepositoryInterface {
     public function find_by_code(string $code): ?object;
     public function find_last_by_contact(string $contact): ?object;
     public function save(string $contact, string $code, int $expires_at, string $type = 'verification'): object;
}