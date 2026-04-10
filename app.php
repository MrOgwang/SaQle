<?php
namespace SaQle;

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
     StorageServiceProvider,
     MiddlewareProvider
};
use SaQle\Http\Cors\CorsConfig;
use SaQle\Core\Support\AppContext;
use SaQle\Core\Config\{ConfigRepository, AppSetup, Config};
use SaQle\Http\Request\{Request, Runtime};
use SaQle\Session\Providers\SessionProvider;
use SaQle\Auth\Guards\GuardManager;
use SaQle\Build\Manage\Manage;

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

         //7. Register framework + app providers
         $this->boot();
     }

     private function initialize() : void {
         require_once __DIR__.'/shortcuts/helpers.php';
         require_once __DIR__.'/shortcuts/strings.php';
         require_once __DIR__.'/shortcuts/routes.php';
         require_once __DIR__.'/shortcuts/dates.php';
         require_once __DIR__.'/shortcuts/arrays.php';
         require_once __DIR__.'/shortcuts/responses.php';
         require_once __DIR__.'/shortcuts/exceptions.php';
         $this->load_environment();
     }

     private function expose_configs(array $config){
         $repo = $this->container->resolve(ConfigRepository::class);
         $repo->merge($config);
     }

     public function boot(): void {
         $framework_providers = [
             FrameworkDIProvider::class,
             EventServiceProvider::class,
             AuthenticationProvider::class,
             SessionProvider::class,
             ValidationServiceProvider::class,
             StorageServiceProvider::class,
             MiddlewareProvider::class
         ];

         foreach(array_merge($framework_providers, $this->setup->providers) as $provider){
             (new $provider($this))->register();
         }
     }

     private function load_environment(): void {
         ($this->setup->environment_loader && is_callable($this->setup->environment_loader)) ? (
             ($this->setup->environment_loader)($this->setup->environment)
         ) : null;
     }
     
     public function run(): void {
         new Runtime()->handle(Request::init());
     }

     public function run_cli($args){
         (new Manage($args))();
     }
}
