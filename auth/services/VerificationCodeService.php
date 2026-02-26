<?php
namespace SaQle\Auth\Services;

use SaQle\Commons\StringUtils;
use SaQle\Auth\Interfaces\VerificationCodeRepositoryInterface;
use RuntimeException;

class VerificationCodeService {
     use StringUtils;

     protected VerificationCodeRepositoryInterface $repository;

     public function __construct(VerificationCodeRepositoryInterface $repository) {
         $this->repository = $repository;
     }

     public function create_code(
         int $length = 30, 
         bool $base64_encode = false, 
         string $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
         int $max_retries = 10
     ) : string {
         $retry = 0;
         do {
             if($retry >= $max_retries){
                 throw new RuntimeException("Unable to generate a unique code after {$max_retries} attempts.");
             }

             $code = $this->random_string2($length, $base64_encode, $characters);
             $retry++;
         } while ($this->repository->find_by_code($code));

         return $code;
     }

     public function save_code(
         string $contact, 
         string $type = 'verification', 
         int $expires_in_seconds = 86400
     ) : object {

         $code_str = match($type){
             'verification' => $this->create_code(5, false, "0123456789"),
             'otp'          => $this->create_code(5, false, "0123456789")
         };

         return $this->repository->save($contact, $code_str, time() + $expires_in_seconds, $type);
     }

     public function confirm_code(string $contact, string $code): void {
         $saved_code = $this->repository->find_last_by_contact($contact);
         if(!$saved_code || $saved_code->code !== $code) {
             throw new RuntimeException("Invalid verification code provided.");
         }
     }
}