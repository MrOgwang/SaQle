<?php

namespace SaQle;

use SaQle\Core\Config\AppSetup;
use SaQle\Core\Services\Container\Container;
use SaQle\Core\Registries\{
    MiddlewareRegistry,
    ObserverRegistry,
    EventRegistry
};
use SaQle\Core\Services\Providers\{
     FrameworkDIProvider, 
     ModelObserverProvider, 
     AuthenticationProvider
};
use SaQle\Http\Cors\CorsConfig;
use SaQle\Core\Support\AppContext;
use SaQle\Core\Config\Config;
use SaQle\Http\Request\{Request, Runtime};
use SaQle\Session\Providers\SessionProvider;
use SaQle\Routes\Providers\RoutingProvider;
use SaQle\Auth\Guards\GuardManager;

final class App{
     public readonly string $environment;
     public MiddlewareRegistry $middleware;
     public Container $container;
     public CorsConfig $cors;
     public GuardManager $guards;
     public EventRegistry $events;

     public function __construct(private AppSetup $setup){
         $this->environment = $setup->environment;
         $this->middleware  = new MiddlewareRegistry();
         $this->container   = new Container();
         $this->cors        = new CorsConfig($setup->cors);
         $this->guards      = new GuardManager();
         $this->events      = new EventRegistry();

         $this->boot();

         AppContext::set($this);
     }

     public function boot(): void {
         require_once __DIR__.'/shortcuts/helpers.php';
         $this->load_environment();
         $this->load_config();
         $this->register_middlewares();
         $this->register_providers();
     }

     private function load_environment(): void {
         ($this->setup->environment_loader && is_callable($this->setup->environment_loader)) ? (
             ($this->setup->environment_loader)($this->setup->environment)
         ) : null;
     }

     private function load_config(): void{
         if($this->setup->config_dir){
             $files = ['app', 'auth', 'database', 'email', 'model', 'tenant', 'session'];
             $configurations = [];
             for($f = 0; $f < count($files); $f++){
                 $file_name = $files[$f];
                 $path = $this->setup->config_dir.'/'.$file_name.'.config.php';
                 if(file_exists($path)){
                     $configurations = array_merge($configurations, require $path);
                 }
             }
             new Config(...$configurations);
         }
     }

     private function register_middlewares(): void {
         foreach ($this->setup->middlewares as $mw) {
             $this->middleware->register($mw);
         }
     }

     private function register_providers(): void {
         $framework_providers = [
             FrameworkDIProvider::class,
             ModelObserverProvider::class,
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
