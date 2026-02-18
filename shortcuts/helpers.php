<?php

use SaQle\Log\FileLogger;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Response\Types\RedirectResponse;
use SaQle\Http\Request\Data\Session;
use SaQle\Core\Config\ConfigRepository;
use SaQle\Http\Request\Request;
use SaQle\Core\Support\AppContext;
use SaQle\App;
use Exception;

if(!function_exists('app')){
     function app() : App {
         return AppContext::get();
     }
}

if(!function_exists('resolve')){
     function resolve(string $abstract, array $parameters = []){
         return app()->container->resolve($abstract, $parameters);
     }
}

if(!function_exists('request')){
     function request(){
         return Request::init();
     }
}

if(!function_exists('file_logger')){
     function file_logger(){
        return resolve(FileLogger::class);
     }
}

if(!function_exists('config')){
     function config(string $key, mixed $default = null): mixed {
         return app()->container->resolve(ConfigRepository::class)->get($key, $default);
     }
}

if(!function_exists('with_config')){
     function with_config(array $overrides, callback $callable): mixed {
         $config = app()->container->resolve(ConfigRepository::class);

         $config->push($overrides);

         try{
             return $callback();
         }finally{
             $config->pop();
         }
     }
}

if(!function_exists('session')){
     function session(): Session {
         return Request::get()->session();
     }
}

if(!function_exists('path_join')){
     function path_join(array $parts, bool $trailing_slash = false): string {
         $separator = DIRECTORY_SEPARATOR;
         $clean = [];

         foreach($parts as $index => $part){
             if($part === '' || $part === null) {
                 continue;
             }

             $part = str_replace(['/', '\\'], $separator, $part);

             if($index === 0){
                 $clean[] = rtrim($part, $separator);
             }else{
                 $clean[] = trim($part, $separator);
             }
         }

         $path = implode($separator, $clean);

         return $trailing_slash ? rtrim($path, '/\\').$separator : $path;
     }
}

if(!function_exists('redirect')){
     function redirect(?string $url = null, int $status = HttpMessage::FOUND, mixed $data = null, ?string $message = null){
         return new RedirectResponse(url: $url, status: $status)->send();
     }
}

if(!function_exists('import_routes')){
     function import_routes(string $app, string $type = 'web'){
         $path = config('base_path').'/apps/'.$app.'/routes/'.$type.'.php';
         if(file_exists($path)){
             return require $path;
         }

         return [];
     }
}

if(!function_exists('to_session')){
     function to_session(string $key, callable $data_source, bool $persistent = false){
         $data = $data_source();

         $request = Request::get();
         $request->session->set($key, $data, $persistent);

         return $data;
     }
}

if(!function_exists('from_session')){
     function from_session(string $key, ?callable $data_source = null, bool $latest = false){
         $request = Request::init();
         if(!$request->session->exists($key) || $latest)
             return $data_source ? $data_source() : null;

         return $request->session->get($key, null);
     }
}

if(!function_exists('str_limit')){
     function str_limit(string $value, int $limit = 100, string $end = '…'): string {
         return mb_strimwidth($value, 0, $limit, $end, 'UTF-8');
     }
}

if(!function_exists('env')){
     function env(string $key, mixed $default): mixed {
         $key = strtoupper($key);
         
         //Prefer $_ENV
         if(isset($_ENV[$key])) return $_ENV[$key];

         //Fallback to getenv()
         $value = getenv($key);
         if($value !== false) return $value;

         //Fallback to $_SERVER (some servers populate env here)
         if(isset($_SERVER[$key])) return $_SERVER[$key];

         //If nothing found, return default
         return $default;
     }
}

if(!function_exists('cli_log')){
     function cli_log(string $message): void {
         fwrite(STDERR, $message . PHP_EOL);
     }
}

if (!function_exists('saqle_validate')){

    /**
     * Global helper to validate a value using a registered validator.
     * Returns boolean only.
     *
     * @param string $rule Rule name registered in the app, e.g., 'email', 'min_length'
     * @param mixed $threshold The threshold/value of the rule, e.g., true, 5, ['red','green']
     * @param mixed $value The value to validate
     * @param array $context Optional context
     * @return bool True if value passes validation, false otherwise
     * @throws Exception
     */
    function saqle_validate(string $rule, mixed $threshold, mixed $value, array $context = []): bool {
        // Fetch app instance (assumes you have a global app helper)
        if (!function_exists('app')) {
            throw new Exception("Global helper 'app()' is required to access validator registry.");
        }

        $app = app(); // returns your SaQle app instance

        // Check if rule exists in registry
        if (!$app->rules->has($rule)) {
            throw new Exception("Validator for rule '{$rule}' is not registered in the app.");
        }

        // Get validator class and instantiate
        $validator_class = $app->rules->get($rule);
        
        $validator = new $validator_class();

        // Run validation
        $result = $validator->validate($rule, $value, $threshold, $context);

        // Return boolean only
        return $result->isvalid;
    }
}

