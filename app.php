<?php

namespace SaQle;

use SaQle\Core\Config\AppSetup;
use SaQle\Core\Services\Container\Container;
use SaQle\Core\Registries\{
    MiddlewareRegistry,
    EventRegistry,
    CachedEventRegistry
};
use SaQle\Core\Services\Providers\{
     FrameworkDIProvider, 
     EventServiceProvider, 
     AuthenticationProvider
};
use SaQle\Http\Cors\CorsConfig;
use SaQle\Core\Support\AppContext;
use SaQle\Core\Config\{ConfigRepository, Config, ConfigDefaults, ConfigBridge};
use SaQle\Http\Request\{Request, Runtime};
use SaQle\Session\Providers\SessionProvider;
use SaQle\Routes\Providers\RoutingProvider;
use SaQle\Auth\Guards\GuardManager;

final class App{
     public readonly string $environment;
     private array $config;
     public MiddlewareRegistry $middleware;
     public Container $container;
     public CorsConfig $cors;
     public GuardManager $guards;
     public CachedEventRegistry $events;

     public function __construct(private AppSetup $setup){
         $this->initialize();

         $this->environment = $setup->environment;
         $this->middleware  = new MiddlewareRegistry();
         $this->cors        = new CorsConfig($setup->cors);
         $this->guards      = new GuardManager();
         $this->container   = new Container();
         $this->events      = new CachedEventRegistry($this->config['document_root'], $this->config['class_mappings_dir']);

         ConfigBridge::expose($this->config);

         $this->boot();

         AppContext::set($this);
     }

     private function initialize() : void {
         require_once __DIR__.'/shortcuts/helpers.php';
         $this->load_environment();
         $this->load_config();
     }

     public function boot(): void {
         $this->container->singleton(ConfigRepository::class, fn() => new ConfigRepository($this->config));
         $this->register_middlewares();
         $this->register_providers();
     }

     private function load_environment(): void {
         ($this->setup->environment_loader && is_callable($this->setup->environment_loader)) ? (
             ($this->setup->environment_loader)($this->setup->environment)
         ) : null;
     }

     private function load_config(): void{
         if(!$this->setup->config_dir) {
             return;
         }

         $files = ['app', 'auth', 'database', 'email', 'model', 'tenant', 'session'];
         $config = [];

         foreach ($files as $file) {
             $path = $this->setup->config_dir."/{$file}.config.php";
             if (file_exists($path)) {
                 $config = array_replace($config, require $path);
             }
         }

         $this->config = ConfigDefaults::merge($config);
     }

     private function register_middlewares(): void {
         foreach ($this->setup->middlewares as $mw) {
             $this->middleware->register($mw);
         }
     }

     private function register_providers(): void {
         $framework_providers = [
             FrameworkDIProvider::class,
             EventServiceProvider::class,
             AuthenticationProvider::class,
             SessionProvider::class,
             //RoutingProvider::class
         ];

         foreach(array_merge($framework_providers, $this->setup->providers) as $provider){
             (new $provider($this))->register();
         }
     }

     public function run(): void {
         new Runtime()->handle(Request::init());
     }
}
