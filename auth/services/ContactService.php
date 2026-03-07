<?php
namespace SaQle\Auth\Services;

use SaQle\Auth\Interfaces\ContactRepositoryInterface;

class ContactService {
     protected ContactRepositoryInterface $repository;

     public function __construct(ContactRepositoryInterface $repository){
         $this->repository = $repository;
     }

     /**
      * Ensures that provided contact doesn't already exist
      * 
      * @param string $contact - the actual contact (email or phone number)
      * @param nullable string $type - the contact type (email, phone)
      * @param nullable string $owner_type - who owns the contact(user, tenant)
      * */
     public function ensure_available(string $contact, ?string $type = null, ?string $owner_type = null): void {
         if($this->repository->exists($contact, $type, $owner_type)){
             bad_request_exception("The provided {$type} already exists.");
         }
     }
}