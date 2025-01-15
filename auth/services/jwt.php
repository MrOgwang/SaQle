<?php
namespace SaQle\Auth\Services;

class Jwt{
     public function __construct(private string $key){

     }

     private function base64_url_encode(string $text): string{
         return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
     }

     public function encode(array $payload): string{
         $header = json_encode([
            "alg" => "HS256",
            "typ" => "JWT"
         ]);

         $header = $this->base64_url_encode($header);
         $payload = json_encode($payload);
         $payload = $this->base64_url_encode($payload);

         $signature = hash_hmac("sha256", $header.".".$payload, $this->key, true);
         $signature = $this->base64_url_encode($signature);
         return $header.".".$payload.".".$signature;
     }

     public function decode(string $token): array{
         if(preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/", $token, $matches) !== 1){
             throw new \Exception("invalid token format");
         }

         $signature = hash_hmac("sha256", $matches["header"].".".$matches["payload"], $this->key, true);
         $signature_from_token = $this->base64_url_decode($matches["signature"]);

         if(!hash_equals($signature, $signature_from_token)) {
             throw new \Exception("JWT signature doesn't match");
         }

         $payload = json_decode($this->base64_url_decode($matches["payload"]), true);

         return $payload;
     }

     private function base64_url_decode(string $text): string{
         return base64_decode(str_replace(["-", "_"], ["+", "/"], $text));
     }
}
?>