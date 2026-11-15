# Components

## What Is A Component?

A component is a self-contained unit of functionality in a SaQle application. It encapsulates the server-side processing, presentation, client-side behaviour, styling, and configuration associated with a particular application feature.

Unlike traditional MVC frameworks, where routes are typically mapped directly to controller actions, SaQle routes are mapped to components.

A component acts as the target of a request. It coordinates the processing of incoming requests and, where applicable, brings together the necessary presentation and client-side resources to produce the appropriate output.

Each component consists primarily of five files:

| File | Responsibility |
|:-------|:------------|
| `Controller.php`    | Handles the server-side application logic associated with the component. |
| `Template.html`     | Defines the component's HTML presentation.|
| `Script.js`         | Defines the component's client-side behaviour and interactions |
| `Style.css`         | Defines the component's visual appearance. |
| `Definition.php`    | Provides the component's definition and configuration. |

A valid component must have a `Definition.php` file and one of `Template.html` or a `Controller.php` file or both. All the other files are optional.

## Understanding Components

Consider a user login page.

In a conventional MVC approach, you might define a route pointing to a controller action:

```php
Router::get('/login', [LoginController::class, 'login']);
```

The controller handles the request, while the corresponding view, JavaScript, and CSS are managed separately.

In SaQle, the login functionality is represented by a component. The route points to that component rather than directly to its controller.

Conceptually:

```php
Router::get('/signin', 'app.auth.signin');
```

The `Signin` component brings together:

1. `Controller.php`: Handles login-related server-side processing.
2. `Template.html`: Defines the login form and its presentation.
3. `Script.js`: Handles client-side form interactions.
4. `Style.css`: Defines the login form's appearance.
5. `Definition.php`: Defines the component's configuration.

This means that when a developer works on the signin component, the files responsible for its different aspects of functionality are organized together.

## Component Naming

Components have a specific way they are named and you must adhere to this naming convention, otherwise your application will not work correctly.

Consider the following project structure:

```text
App/
├── config/
├── public/
└── src/
    ├── Bootstrap/
    ├── Components/
    ├── Databases/
    └── Modules/
        ├── Auth/
        │   ├── Components/
        │   │   └── Signin/
        │   │       ├── Controller.php
        │   │       ├── Template.html
        │   │       ├── Script.js
        │   │       ├── Style.css
        │   │       └── Definition.php
        │   │
        │   ├── Middleware/
        │   ├── Validators/
        │   ├── Guards/
        │   └── Contracts/
        │
        ├── Billing/
        └── Inventory/
```

There are two groups of components in a SaQle application. 

- The first group is developer defined components, which must have the `app` prefix to their names.
- The other group is framework defined components. These are components that come with the framework code. These have a `saqle` prefix to their names.

Every component in your project, even the ones that come with the framework, belongs to a particular module. In the project structure above, the `Signin` component belongs to the `Auth` module.

Together, these form a unique component name: `app.auth.signin`. The component name must be in all lowercase. 

## Creating A Component

Inside the module directory in which you want to create the component, you can manually create a new folder, give it a name and add `Controller.php`, `Template.html` and any other relevant component files to it.

Alternatively, you can run the following command from your project root to quickly create a new component.

```
php saqle make:component Signin Auth
```

`Signin` is the name of the component and `Auth` is the module name. The component will be created in the right module if that module exists. If the module doesn't exist, a new module called `Auth` will be created as well.

## How Components Work

Consider this route:

```php
Router::get('/signin', 'app.auth.signin');
```

::: info
Components are validated, compiled and cached before a request runs. By the time your app is running there is guarantee
the component has either a `Controller.php` file, a `Template.html` file or both.
:::

### Web Request

When a `GET` request is sent to the url `/signin`:

1. The framework checks whether the `app.auth.component` has defined a `Controller.php` file. 
2. If no controller is defined for this component, the `Template.html` is rendered as a response alongside the associated `Script.js` and `Style.css`. 
3. If a controller is defined for this component, the framework checks whether a `public function get()` method is defined on the controller.
4. If the `public function get` method is not found, an exception is thrown. If the method exists, the method will be called to return a `Message` object.
5. The `Message` object will provide a context array. 
6. The transport layer uses the context array to fill in placeholders in the `Template.html` file, which is then rendered as a response alongside the associated `Script.js` and `Style.css`. 

::: tip
`Message` object is what controller actions return. This represents an outcome of a controller action, and unlike controllers in other frameworks, this is not a response yet. We will discuss the `Message` object further in the upcoming sections.
:::

### API Request

When a `GET` request is sent to the url `/api/v1/signin`:

1. For API requests, the component must defined a `Controller.php` file. 
2. The framework checks whether a `public function get()` method is defined on the controller.
3. If the `public function get` method is not found, an exception is thrown. If the method exists, the method will be called to return a `Message` object.
4. The `Message` object will provide a context array. 
5. The transport layer converts this context array to JSON response and returns to the client. 

::: tip
SaQle doesn't have separate routes for web and api requests. Routes are defined once and handle both requests. Therefore this route
```php
Router::get('/signin', 'app.auth.signin');
```
will handle an api request sent to the url `/api/v1/signin`. Learn more about how this works in [Routing](/fundamentals/routing).
:::

## Component Parts

We will stick to the `Signin` component as an example to discuss these sections.

### Controller

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
             return Message::unauthorized('Invalid user name or password');
         }

         $result->next = $next;

         return Message::ok('Login successful')->with('result', $result);
     }
}
```


### Template

### Definition

### Script

### Style

### Assets

### Themes

## The Message Object
