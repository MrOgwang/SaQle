<?php

namespace SaQle\Admin\Resources;

class ResourceDefinition {

     protected string $name;

     /**
     * @var ResourceOperation[]
     */
     protected array $operations = [];

     public function __construct(string $name){
         
         $this->name = $name;

         foreach ([
            'list',
            'show',
            'create',
            'edit',
            'delete'
         ] as $operation){

             $this->operations[$operation] = new ResourceOperation($operation);

         }
     }

     public function name(): string {
         return $this->name;
     }

     public function list(): ResourceOperation {
         return $this->operations['list'];
     }

     public function show(): ResourceOperation {
         return $this->operations['show'];
     }

     public function create(): ResourceOperation {
         return $this->operations['create'];
     }

     public function edit(): ResourceOperation {
         return $this->operations['edit'];
     }

     public function delete(): ResourceOperation {
         return $this->operations['delete'];
     }

     public function operation(string $name): ?ResourceOperation {
         return $this->operations[$name] ?? null;
     }

     /**
     * @return ResourceOperation[]
     */
     public function operations(): array {
         return $this->operations;
     }
}