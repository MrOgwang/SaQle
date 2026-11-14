# Routing

Routing is the mechanism by which you direct a request to a given component for execution. 

::: tip
In other PHP frameworks, a route is often pointed at a controller. While a controller is a part of a component in SaQle, routes are pointed at components instead of controllers.

Learn more about [Components](components.md).
:::

## Defining Routes

### Convention

Routes are defined in the `Routes.php` file inside the `Routes` directory. Each module added to the project may have only one routes file.

### Configuration

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

### Component Routes


## Route Grouping

## Route Functionalities

### Generating URLs To Named Routes

### Inspecting The Current Route