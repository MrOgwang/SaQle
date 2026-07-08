<?php
namespace SaQle\Auth\Services;

use SaQle\Auth\Interfaces\UserRepositoryInterface;
use RuntimeException;

class AccountValidationService {
     protected UserRepositoryInterface $repository;

     public function __construct(UserRepositoryInterface $repository){
         $this->repository = $repository;
     }

     public function confirm_username(string $username): void {
         if($this->repository->find_by_username($username)){
             throw new RuntimeException("This username is already taken.");
         }
     }
}