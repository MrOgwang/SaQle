# Routing

Routing is the mechanism by which you direct a request to a given component for execution. 

::: tip
In other PHP frameworks, a route is often pointed at a controller. While a controller is a part of a component in SaQle, routes are pointed at components instead of controllers.

Learn more about [Components](components.md).
:::

## Route Configuration

In **_Config/App.php_** configuration file, the following settings are available for routes:

1. _api_url_prefixes_: a string array of the prefixes to be attached to your api endpoints.
2. _modules_: a list of module classes installed for your application.


```php
<?php
namespace App\Config;

use App\Modules\Auth\Auth;
use App\Modules\Inventory\Inventory;

return [
     //api url prefixes
     "api_url_prefixes" => ['/api/v1/', '/api/v2/'],

     //list installed modules
     'modules' => [
         Auth::class,
         Inventory::class
     ],

]
?>
``` 
::: tip
Routes are automatically loaded, compiled and cached as long as the module where the routes are defined has been listed. Therefore it is important that you list all the modules you have installed for your application.
:::


## File Routes

Routes are defined in the `Routes.php` file inside the `Routes` directory. Each module added to the project may have only one routes file.

### Basic Routing

The most basic route accepts a URI and a component name.

```php
use SaQle\Routing\Router;

Router::get("/signin", 'app.auth.signin');
```

::: tip
The component reference `app.auth.signin` means this is an `Application` component called `Signin` defined in the `Auth` module of your application. 

Learn more about [Components](components.md) and [Modules](components.md).
:::

### The Routing Methods

You can define routes that respond to any HTTP verb as shown below:

```php

use SaQle\Routing\Router;

Router::get($uri, $component);
Router::post($uri, $component);
Router::patch($uri, $component);
Router::put($uri, $component);
Router::delete($uri, $component);
Router::query($uri, $component);
Router::options($uri, $component);
```

Sometimes you want to define a route that responds to multiple HTTP verbs. Use the **route** method together with
the **methods** method.

```php
use SaQle\Routing\Router;

Router::route("/signup", 'app.auth.signup')
     ->methods(function(){
         Router::method("GET");
         Router::method("POST");
     });
```

### Route Target

When you define a route like this:


```php
use SaQle\Routing\Router;

Router::get("/signin", 'app.auth.signin');
```

The framework assumes that there is a corresponding `public function get()` method defined in the component's `Controller.php` file. If this method is not found, an exception is thrown.

This is true for `post`, `put`, `patch`, `delete`, `options`, `query` HTTP verbs as well.

You can specify a custom action to execute like this:

```php
use SaQle\Routing\Router;

Router::get("/signin", 'app.auth.signin@show_form');
```

The `public function show_form()` method must exist on the component's `Controller.php` file, otherwise an exception is thrown.



### API Routes

Unlike other PHP frameworks which may separate API and Web routes definition, in SaQle, there is no distinction
between API and Web routes definition. 

The same routes defined once will work for your API requests as well. The only thing you need to do is append
one of the __api_url_prefixes__ values to the request URI, and SaQle processes it as an API request.

For example, instead of sending a request to `/signin`, send the request to `/api/v1/signin` to process that request as an API request.

### View Routes

You do not need to do anything special to return views. Once you have provided a component name to the route definition, the framework returns views in this manner:

1. If the component has a `Controller.php` file, the framework will execute the action associated with the route and return some context.
2. The context is used to fill in the `Template.html` placeholders and a view is rendered.
3. In cases where the component has not defined a `Controller.php` file, the `Template.html` is just rendered immediately as a view.

### Route Parameters

Sometimes you want to capture segments of the URI within your route. For example, you may need to capture a user's ID from the URL. You may do so by defining route parameters:

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id", 'app.auth.profile');
```

You may define as many route parameters as required by your route.

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id/section/:section_id", 'app.auth.profile@show_profile');
```

Route parameters start with : and only consist of alphabetic characters and underscores. 

The route parameters are injected into the component's controller based on their order and the names used to define them.

```php
<?php
namespace App\Modules\Auth\Components\Profile;

use SaQle\Http\Message\Message;

final class Controller {
     public function show_profile(string $user_id, string $section_id) : Message {
         //work some magic here
         return Message::ok();
     }
}
```

#### Optional Route Parameters

Sometimes you can define a route parameter that may not always be present in the URL

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id/section/:section_id?", 'app.auth.profile@show_profile');
```

You need to adjust the controller action method to accomodate this as well.

```php
 public function show_profile(string $user_id, ?string $section_id = null) : Message {
     $section_id = $section_id ?? 'personal';
     //work some magic here
     return Message::ok();
 }
```

You can also provide a default value for an optional route parameter.

```php
 public function show_profile(string $user_id, string $section_id = 'personal') : Message {
     //work some magic here
     return Message::ok();
 }
```


### Named Routes

Named routes allow the convenient generation of URLs or redirects for specific routes. You may specify a name for a route by using the `name` method on the route definition:

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id", 'app.auth.profile')
->name('user.profile.show');
```
### Route Prefix

provide a prefix to the route by using the `prefix` method.

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id", 'app.auth.profile')->prefix('/auth');
```

### Route Middleware

Use the `middleware` method to add route specific middleware when defining routes. 

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id", 'app.auth.profile')
->middleware(['authentication', 'authorization'])
->name('user.profile.show');
```

::: tip
You must first define your middleware and register it with the application before you can add it to your route.

Learn more about [Middleware](components.md).
:::

### Route Permissions

Protect your routes with permission guards by using the `authorize` method on your route definition.

```php
use SaQle\Routing\Router;

Router::get("/profile/:user_id", 'app.auth.profile')
->middleware(['authentication', 'authorization'])
->authorize('authenticated && admin')
->name('user.profile.show');
```

::: tip
The guards `authenticated` and `admin` must first be defined and registered with the application.

Learn more about [Guards](components.md).
:::

### Server Sent Event Routes

Use the `sse` method on the route definition to customize server sent event routes. The method takes in a string `$event_name` and an integer `$interval`.

```php
use SaQle\Routing\Router; 

Router::get("/events/notifications", 'app.feed.notifications@stream')
->sse('notification', 3);
```

### Layouts


### Route Model Binding

For resource routes, a model is bound to the route by providing a third argument to any of the router `post`, `patch`, `put`, `get`, `delete` methods.

```php
use SaQle\Routing\Router;
use App\Modules\Auth\Models\User;

Router::get("/user/:user_id", 'app.auth.profile', User::class);
```

You can also use the `model` method.

```php
use SaQle\Routing\Router;
use App\Modules\Auth\Models\User;

Router::get("/user/:user_id", 'app.auth.profile')->model(User::class);
```

## Component Routes

SaQle provides you ways to define routes directly on a component's `Controller.php` file, alongside the actions to be executed for these routes.

Use the attributes `Get`, `Post`, `Patch`, `Put`, `Delete`, `Query` to define routes on a component's `Controller.php` methods.

Take this `Controller.php` in the `Signin` component defined in the `Auth` module:

```php
<?php
namespace App\Modules\Auth\Components\Signin;

use App\Modules\Auth\Services\AuthenticationService;
use SaQle\Http\Message\Message;
use SaQle\Http\Attributes\{
     Get, 
     Post
};

final class Controller {

     //dependency injection
     public function __construct(
         private AuthenticationService $service
     ){}

     //handle get request
     #[Get(url: '/signin', name: 'signin.form')]
     public function display_form() : Message {
         return Message::ok();
     }

     //handle post request
     #[Post(url: '/signin', name: 'signin.submit')]
     public function signin(string $username, string $password, ?string $next = null) : Message {

         $result = $this->service->login(
             strategy: 'password', 
             credentials: [
                 'username' => $username, 
                 'password' => $password
             ]
         );

         if(!$result->success){
             return Message::bad_request('Invalid credentials!');
         }

         $result->next = $next;

         return Message::ok('Login successful!')->with('result', $result);
     }
}
```

When the app is built, two routes will be compiled and cached:

1. GET: /signin
2. POST: /signin

If these routes were defined in the `Routes.php` file, we would have something like this:

```php
use SaQle\Routing\Router;

Router::route("/signin", 'app.auth.signin')
     ->methods(function(){
         Router::method("GET", "display_form")->name('signin.form');
         Router::method("POST", "signin")->name('signin.submit');
     });
```

Apart from `string $url` and `string $name`, the attributes `Get`, `Post`, `Patch`, `Put`, `Delete`, `Query` also accept additional attributes:

1. `string $authorize = ""`: define the permission guards to protect route
2. `array $middleware = []`: define route specific middleware
3. `array $layout = []`: define parent layout components
4. `?string $model = null`: bind a model class to the route in case of a resource route

::: tip
Whether you define your routes in the `Routes.php` file or on the `Controller.php` with attributes is a case of personal preference. After all, all routes are first compiled and cached before your application runs.

However, if you want to see all your routes in one place at a glance, defining them in a `Routes.php` file makes more sense. Defining them in a file also allows you to group routes that share context together.
:::

## Route Grouping

In large applications, you can easily end up with upwards of hundreds of routes. Soon you realize that most of your routes share similar context, and instead of repeatedly defining these context individually per route, you can group routes that share similar context together.

Use the `context` method chained with the `routes` method to group routes that share similar context together. The `context` method takes in an `array` of context to be shared by the routes.

Consider an application with several administrator routes. You want that the administrator routes be accessible only when one is logged in and is an admin.

```php
use SaQle\Routing\Router;

Router::context([
     'middleware' => ['authentication', 'authorization'],
     'authorize'  => 'authenticated && admin',
     'prefix'     => '/admin',
     'name'       => 'admin'
])->routes(function(){
     Router::get('/users', 'app.admin.users')->name('users.show');

     Router::context([
        'prefix' => '/settings',
        'name'   => 'settings'
     ])->routes(function(){
         Router::get('/general', 'app.admin.settings@general')->name('general');
         Router::get('/notifications', 'app.admin.settings@notifications')->name('notifications');
         Router::get('/security', 'app.admin.settings@security')->name('security');
     });
});
```

We will keep this example short, but as you can see, it is possible to group within a group. 

When the above route definitions are compiled, we end up with the following routes:

| Route | Name | Middleware | Guards |
|:-------|:------------|:------:|:--------:|
| `GET: /admin/users`    | `admin.users.show` | `authentication, authorization` | `authenticated, admin` |
| `GET: /admin/settings/general`   | `admin.settings.general` | `authentication, authorization` | `authenticated, admin` |
| `GET: /admin/settings/notifications`   | `admin.settings.notifications` | `authentication, authorization` | `authenticated, admin` |
| `GET: /admin/settings/security`   | `admin.settings.security` | `authentication, authorization` | `authenticated, admin` |

::: tip
While it is possible to nest groups on many levels, we recommend keeping nesting to a maximum of three levels deep only. 
:::

## Working With Routes

### Generating URLs To Named Routes

When you give a route a name, you can generate its url using the `route` helper function.

```php
$url = route('signin.form');
return Message::redirect($url)
```

If the named route defines parameters, pass the parameters as the second argument to the route function. The given parameters will automatically be inserted into the generated URL in their correct positions.

```php
use SaQle\Routing\Router;

Router::get(
     "/profile/:user_id/section/:section_id", 
     'app.auth.profile@show_profile'
)->name('user.profile');

//somwhere else

$url = route('user.profile', ['user_id' => 10, 'section_id' => 'general']);

// http://myapp.com/profile/10/section/general
```

### Inspecting The Current Route

if you would like to determine whether the current request matches a certain route, you can use the `is_route` helper method. It takes in a name and determines whether the current request matches that route.

```php
//somwhere in a middleware, controller or wherever

if(is_route('user.profile')){
    //this is a user profile route
}
```

### Get The Route Name From A URL

Given a URL and an HTTP verb, determine the route name assigned using the `route_name` helper method.
```php
//somwhere in a middleware, controller or wherever

$name = route_name('/profile/:user_id/section/:section_id', 'GET');

//user.profile
```

### Accessing The Current Route

Use the `request` helper method to grab the current route from the request instance.

```php

//route name
$name = request()->route->name;

//route url
$url = request()->route->url;
$url = request()->uri();

//the HTTP verb
$method = request()->route->method;
$method = request()->method();

//the component to execute request
$target = request()->route->target;

//url params
$params = request()->route->params;

//url queries
$queries = request()->route->queries;

//for api requests, e.g api/v1
$prefix = request()->route->prefix; 

//all middleware that will run for this route
$middleware = request()->route->middleware; 

//all the permission guards assigned to this route
$guards = request()->route->guards;

//the model bound to this route, if any
$model = request()->route->model_class;

//the layout components associated with this route
$layout = request()->route->layout;

//server sent event customization if this is an sse route
$sse = request()->route->sse;
``` 