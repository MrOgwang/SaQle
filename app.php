<?php

namespace SaQle;

use SaQle\Core\Config\AppSetup;
use SaQle\Core\Services\Container\Container;
use SaQle\Core\Registries\{
    MiddlewareRegistry,
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
     StorageServiceProvider
};
use SaQle\Http\Cors\CorsConfig;
use SaQle\Core\Support\AppContext;
use SaQle\Core\Config\{ConfigRepository, Config, ConfigBridge};
use SaQle\Http\Request\{Request, Runtime};
use SaQle\Session\Providers\SessionProvider;
use SaQle\Routes\Providers\RoutingProvider;
use SaQle\Auth\Guards\GuardManager;

final class App {
     public MiddlewareRegistry $middleware;
     public Container $container;
     public CorsConfig $cors;
     public GuardManager $guards;
     public CachedEventRegistry $events;
     public RuleHandlerRegistry $rules;
     public StorageRegistry     $disks;

     public function __construct(private AppSetup $setup){
         // 1. Expose app early
         AppContext::set($this);

         // 2. Initialize CORE infrastructure FIRST
         $this->container  = new Container();
         $this->middleware = new MiddlewareRegistry();
         $this->guards     = new GuardManager();
         $this->rules      = new RuleHandlerRegistry();
         $this->disks      = new StorageRegistry();

         // 3. Load environment & helpers
         $this->initialize();

         // 4. Load config
         $config = $setup->get_configurations();
         ConfigBridge::expose($config);

         // 5. Remaining services
         $this->cors   = new CorsConfig($setup->cors);
         $this->events = new CachedEventRegistry(
             $config['base_path'],
             $config['class_mappings_dir']
         );

         // 6. Register framework + app providers
         $this->boot($config);
     }

     private function initialize() : void {
         require_once __DIR__.'/shortcuts/helpers.php';
         require_once __DIR__.'/shortcuts/strings.php';
         require_once __DIR__.'/shortcuts/arrays.php';
         $this->load_environment();
     }

     public function boot(array $config): void {
         $this->container->singleton(ConfigRepository::class, fn() => new ConfigRepository($config));
         $this->register_middlewares();
         $this->register_providers();
     }

     private function load_environment(): void {
         ($this->setup->environment_loader && is_callable($this->setup->environment_loader)) ? (
             ($this->setup->environment_loader)($this->setup->environment)
         ) : null;
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
             ValidationServiceProvider::class,
             StorageServiceProvider::class
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
