<?php
namespace SaQle\Auth\Identity\Providers;

use SaQle\Auth\Interfaces\{
     UserInterface,
     IdentityProviderInterface
};

class JwtIdentityProvider implements IdentityProviderInterface {
     public function create(UserInterface $user): string{
         $issuer     = config('auth.jwt_iss') ?? config('app.root_domain'); //the domain issuing the token
         $issued_at  = time();                          //the time issued in secends
         $not_before = time();                          //the time in seconds before which token is not valid
         $expires_at = $issued_at + (config('auth.jwt_ttl') * 60);     //time to expire token
         
         $payload = [
             'iat'       => $issued_at,
             'iss'       => $issuer,
             'nbf'       => $not_before,
             'exp'       => $expires_at,
             'user_id'   => $user->user_id,
             'sub'       => $user->user_id,
             'user_name' => $user->username
         ];

         $token = $this->encode($payload);
         return $token;
     }

     public function regenerate() : void {
    
     }

     public function user_id(): ?string{
         $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

         if(!$auth_header)
             authorization_exception("No authorization token provided for request")->throw();

         if(!preg_match('/^Bearer\s+(\S+)$/', $auth_header, $matches))
             bad_request_exception("Incomplete authorization header")->throw();

         $token = $matches[1];
         $data = $this->decode($token);

         //check that token is not expired
         if(array_key_exists("exp", $data) && time() > $data['exp'])
             authorization_exception("Authorization token expired!")->throw();

         return $data['sub'] ?? null;
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

         $signature = hash_hmac("sha256", $header.".".$payload, config('auth.jwt_key'), true);
         $signature = $this->base64_url_encode($signature);
         return $header.".".$payload.".".$signature;
     }

     public function decode(string $token): array{
         if(preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/", $token, $matches) !== 1){
             throw new \Exception("invalid token format");
         }

         $signature = hash_hmac("sha256", $matches["header"].".".$matches["payload"], config('auth.jwt_key'), true);
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

     public function destroy() : void {
         //destry jwt session
     }
}
