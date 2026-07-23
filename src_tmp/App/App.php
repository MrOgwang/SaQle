<?php
namespace SaQle\App;

use SaQle\Core\Services\Container\Container;
use SaQle\Core\Registries\{
    HttpMiddlewareRegistry,
    ConsoleMiddlewareRegistry,
    EventRegistry,
    CachedEventRegistry,
    RuleHandlerRegistry,
    StorageRegistry
};
use SaQle\Core\Services\Providers\{
     FrameworkDIProvider, 
     EventServiceProvider, 
     AuthenticationProvider,
     ValidationServiceProvider,
     StorageServiceProvider,
     TemplateServiceProvider
};
use SaQle\Console\CommandRegistry;
use SaQle\Http\Cors\CorsConfig;
use SaQle\Core\Support\{
     AppContext,
     AppStage,
     Db
};
use SaQle\Core\Config\{
     ConfigRepository, 
     Config
};
use SaQle\Http\Request\Request;
use SaQle\Http\Kernel\HttpKernel;
use SaQle\Console\Kernel\CliKernel;
use SaQle\Session\Providers\SessionProvider;
use SaQle\Auth\Guards\GuardManager;
use SaQle\Http\Request\RequestScope;
use SaQle\Auth\Middleware\{
     PlatformAuthenticationMiddleware,
     PlatformAuthorizationMiddleware
};
use SaQle\Auth\Providers\PlatformAuthorizationProvider;
use SaQle\Console\Providers\FrameworkCommandsProvider;

final class App {

     private AppStage $stage;

     public HttpMiddlewareRegistry $http_middleware;

     public ConsoleMiddlewareRegistry $console_middleware;

     public CommandRegistry $commands;

     public Container $container;

     public CorsConfig $cors;

     public GuardManager $guards;

     public CachedEventRegistry $events;

     public RuleHandlerRegistry $rules;

     public StorageRegistry $disks;

     public function __construct(private AppSetup $setup){

         $this->set_stage(AppStage::INITIALIZING);

         // 1. Expose app early
         AppContext::set($this);

         // 2. Initialize CORE infrastructure FIRST
         $this->container = new Container();
         $this->http_middleware = new HttpMiddlewareRegistry();
         $this->console_middleware = new ConsoleMiddlewareRegistry();
         $this->commands = new CommandRegistry();
         $this->guards = new GuardManager();
         $this->rules = new RuleHandlerRegistry();
         $this->disks = new StorageRegistry();

         $this->container->singleton(ConfigRepository::class, fn() => new ConfigRepository([]));

         //2. load framework configurations
         $config = $setup->get_framework_configs();
         $this->expose_configs($config);

         //4. Load environment & helpers
         $this->initialize();

         //5. Load project configurations
         $config = array_merge($config, $setup->get_project_configs());
         $this->expose_configs($config);

         //6. Remaining services
         $this->cors   = new CorsConfig($setup->cors);
         $this->events = new CachedEventRegistry();

         //7. register middleware
         $this->register_http_middleware();
         $this->register_console_middleware();

         //8. unpack framework + app providers
         $this->unpack_providers();
     }

     private function initialize() : void {
         $shortcuts = [
             'Helpers',
             'Strings',
             'Routes',
             'Dates',
             'Arrays',
             'Exceptions',
             'Session'
         ];

         foreach($shortcuts as $s){
             require_once __DIR__.'/../Shortcuts/'.$s.'.php';
         }

         $this->load_environment();
     }

     private function expose_configs(array $config){
         $repo = $this->container->resolve(ConfigRepository::class);
         $repo->merge($config);
         $repo->resolve_closures();
         $repo->resolve_references();
     }

     private function unpack_providers(): void {

         $framework_providers = [
             FrameworkDIProvider::class,
             EventServiceProvider::class,
             AuthenticationProvider::class,
             SessionProvider::class,
             ValidationServiceProvider::class,
             StorageServiceProvider::class,
             TemplateServiceProvider::class,
             PlatformAuthorizationProvider::class,
             FrameworkCommandsProvider::class
         ];

         foreach(array_merge($framework_providers, $this->setup->providers) as $provider){
             (new $provider($this))->register();
         }

         Db::register_system_db();
     }

     private function register_http_middleware(){

         /**
          * Authentication and Authorization middlewares for the platform
          * adminsitrator:
          * 
          * NOTE: This is probably a bad design. Should look into this further
          * */
         $this->http_middleware->add('__authentication__', PlatformAuthenticationMiddleware::class);
         $this->http_middleware->add('__authorization__', PlatformAuthorizationMiddleware::class);

         if($this->setup->http_middleware){
             foreach($this->setup->http_middleware->all() as $n => $m){
                 $scope = $m['scope'] !== null ? RequestScope::from($m['scope']) : null;
                 $this->http_middleware->add($n, $m['middleware'], $scope);
             }

             $this->http_middleware->set_global($this->setup->http_middleware->get_global());
         }
     }

     private function register_console_middleware(){

         if($this->setup->console_middleware){
             foreach($this->setup->console_middleware->all() as $n => $m){
                 $scope = $m['scope'] !== null ? RequestScope::from($m['scope']) : null;
                 $this->console_middleware->add($n, $m['middleware'], $scope);
             }

             $this->console_middleware->set_global($this->setup->console_middleware->get_global());
         }
     }

     private function load_environment(): void {
         ($this->setup->environment_loader && is_callable($this->setup->environment_loader)) ? (
             ($this->setup->environment_loader)($this->setup->environment)
         ) : null;
     }

     public function set_stage(AppStage $stage): void {
         $this->stage = $stage;
     }

     public function get_stage(): AppStage {
         return $this->stage;
     }  

     public function is_stage(AppStage $stage): bool {
         return $this->stage === $stage;
     }
     
     public function run($args = null) : void {
         if(PHP_SAPI === 'cli'){
             new CliKernel()->process($args);
             exit();
         }

         new HttpKernel()->process();
     }

     public static function http(string $base_path): HttpAppBuilder {
         return new HttpAppBuilder($base_path);
     }

     public static function console(string $base_path): ConsoleAppBuilder {
         return new ConsoleAppBuilder($base_path);
     }

}
