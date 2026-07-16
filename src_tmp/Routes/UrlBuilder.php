<?php

namespace SaQle\Routes;

class UrlBuilder {

     protected string $url;

     protected array $query = [];

     public function __construct(string $url){

         $parts = parse_url($url);

         $this->url = $parts['path'] ?? '';

         if(isset($parts['query'])){
             parse_str($parts['query'], $this->query);
         }
     }

     public function param(string $name, mixed $value): static{

         $this->query[$name] = $value;

         return $this;
     }

     public function filter(
         string $field,
         mixed $value,
         string $operator = "eq",
     ) : static {

         $this->query['filter'] ??= [];

         if(!is_array($this->query['filter'])){
             $this->query['filter'] = [$this->query['filter']];
         }

         $this->query['filter'][] = rawurlencode($field).':'.rawurlencode($operator).':'.rawurlencode((string)$value);

         return $this;
     }

     public function sort(string $field) : static {

         $this->query['sort'] ??= [];

         if(!is_array($this->query['sort'])){
             $this->query['sort'] = explode(',', $this->query['sort']);
         }

         $this->query['sort'][] = $field;

         return $this;
     }

     public function page(int $page): static{

         $this->query['page'] = $page;

         return $this;
     }

     public function records(int $records): static {

         $this->query['records'] = $records;

         return $this;
     }

     public function include(string ...$relations): static {

         $this->query['include'] = implode(',', $relations);

         return $this;
     }

     public function fields(string ...$fields): static {

         $this->query['fields'] = implode(',', $fields);

         return $this;
     }

     public function build(): string {

         $query = $this->query;

         if(isset($query['sort']) && is_array($query['sort'])){
             $query['sort'] = implode(',', $query['sort']);
         }

         $qs = http_build_query($query);

         return $this->url . ($qs ? "?{$qs}" : '');
     }

     public function __toString(): string{
         return $this->build();
     }
}