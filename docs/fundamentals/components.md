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

The component's controller is represented by a file literally called `Controller.php`. And no, you cannot name it something else. Once you have given your component a unique name like `Signin`, the component parts will have standard names.

The controller is the component part that is responsible for processing a request and returning some data. SaQle controllers work the same way as other framework controllers. The only difference, which is an important difference, is that SaQle controller actions return a `Message` object, instead of a response or view. The `Message` object represents an outcome of an action. 

The transport layer decides later on whether to return a JSON or HTML response.

::: tip
Your component is not required to have a controller as long as there is a template, unless your component also processes API requests.
:::

```php
<?php
namespace App\Modules\Auth\Components\Signin;

use App\Modules\Auth\Services\AuthenticationService;
use SaQle\Http\Message\Message;

final class Controller {

     //dependency injection
     public function __construct(
         private AuthenticationService $service
     ){}

     public function get() : Message {
         return Message::ok();
     }

     public function post(string $username, string $password, ?string $next = null) : Message {

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

The component's template is represented by a file literally called `Template.html`, what other frameworks would call a view. This is basically your component's UI.

Your component is not required to have a template. A component that doesn't have a template can only process API requests.

```html
<form method='POST'>
	 <div class='flex center'>
	 	 <h3>Welcome Back</h3>
	 </div>
	 <div class='flex center'>
	 	 <p>Log into your account</p>
	 </div>
	 <div>
	 	 @notify
	 	 @csrf
	 	 <ui:component name="saqle.lib.formfield" bind='app.auth.user:username' label="User name"/>
	 	 <ui:component name="saqle.lib.formfield" bind='app.auth.user:password'/>
	 	 <button type='submit'>Signin</button>
	 </div>
</form>
```

The syntax in this html file may look a little strange. Let me explain whats happening here:

1. `@notify` 

Remember this from the controller:
```php
if(!$result->success){
     return Message::unauthorized('Invalid user name or password');
}
```

The **@notify** tag automatically adds a message in that exact place in case the sign in attempt fails.

2. `@csrf`

This inserts a hidden input with a CSRF token value that will be validated when you post the form.

3. `<ui:component name="saqle.lib.formfield" bind='app.auth.user:username' label="User name"/>`

We are using another component on our signin UI. This is the `saqle.lib.formfield` component. We have discussed how components are named. This is the `FormField` component, from the module `Lib`(The standard SaQle Library) provided by the framework hence the prefix `saqle`.

This tags gives you a user name input field. 

4. `<ui:component name="saqle.lib.formfield" bind='app.auth.user:password'/>`

This will give you a password input field.

We will discuss further how to nest components to construct complete UIs, and the `bind` attribute on on form field components in later sections.

::: tip
A component's `Template.html` file should only contain the html fragment relevant to that particular component. Notice there are no `<html><body></body></html>` tags. These will be provided for by the frameworks `saqle.lib.page` component.

Learn more about how this works in [Layouts](layouts.md).
:::

### Definition

Every SaQle component has a `Definition.php` file that defines its configuration, dependencies, routing behaviour, and other component-specific characteristics.

The definition file is where developers customize how a component is initialized and how it integrates with the application. It provides a centralized place to configure component properties, select templates, declare resource dependencies, and customize component routes.

A component definition extends `AbstractDefinition` from the `SaQle\Core\Components\Abstracts` namespace, which provides a set of methods that can be overridden to customize the component's behaviour.

#### Responsibilities

A component definition can be used to:
- Initialize component properties: Supply initial properties that can be accessed through the component's context.
- Select templates: Determine which template should be rendered, particularly when a component provides multiple template variations.
- Declare dependencies: Specify the JavaScript and CSS resources required by the component.
- Configure routes: Define or customize the routes associated with the component.
- Select a theme: Specify the theme under which the component should be rendered.
- Finalize initialization: Perform additional component-specific setup through the finalization lifecycle hook.

#### Definition Methods

The `AbstractDefinition` class provides the following methods:

##### `initialize(array $props)`
```php
protected function initialize(array $props = []) : void
```
Initializes the component with the supplied properties. Component properties include those provided when a component is declared in UI via the `<ui:component/>` tag.

##### `finalize()`	

```php
public function finalize() : void
```

Provides a lifecycle hook for final component setup.

##### `path(?string $append)`

```php
final public function path(?string $append = null) : string
```

Returns the component's directory path, optionally with an appended path.

##### `name()`

```php
final public function name() : string
```

Returns the component's name.

##### `template(Request $request)`

```php
public function template(Request $request): ?string
```

Determines which template variation should be used.

##### `dependencies()`

```php
public function dependencies() : array
```

Returns the component's external assets (images, css and js) dependencies.

##### `routes()`

```php
public function routes() : array
```

Configure the structure of component routes.

##### `theme()`

```php
 public function theme(Request $request) : string
```

Returns the theme to use when rendering the component.

##### `context()`

```php
final protected function context() : Context
```

Set and access component context and properties.

Some methods are final and are therefore not intended to be overridden. These include `path()`, `name()` and `context()`.

### Script

The component's script is represented by a file literally called `Script.js`. This handles client-side behavior and interactions. This file is not required to exist for a component to be valid.

### Style

The component's style is represented by a file literally called `Style.css`. This is your UI styling and presentation. This file is not required to exist for a component to be valid.

### Assets

Apart from `Script.js` and `Style.css` which get a prominent place in a component, all other assets are placed in a folder called `Assets`. Anything else that your component needs to render its interface, provide functionality, present content, or enhance the user experience that is not a component style sheet or script, must be placed here. These may include images, icons, fonts, videos, audio, documents and data files.

Consider the `Signin` component structure below:

```text
App/
└── src/
    └── Modules/
        ├── Auth/
        │   ├── Components/
        │   │   └── Signin/
        │   │       ├── Assets
        │   │       │   └──Images
        │   │       │      └──handwave.png
        │   │       ├── Controller.php
        │   │       ├── Template.html
        │   │       ├── Script.js
        │   │       ├── Style.css
        │   │       └── Definition.php
```

Unlike public assets, component assets cannot be accessed directly. Call the `asset` helper method to provide you a secure link to a component asset. The method takes in a **path** and an optional **component**.

```php
function asset(string $path, ?string $component = null) : string 
``` 

For example, I want to use the `handwave.png` icon on the `Signin` component's UI. Add this to the `Template.html` file.

```html
<form method="POST">
     <div class="flex center">
         <img src="{{ asset('/Images/handwave.png', 'app.auth.signin') }}">
     </div>
     <div class="flex center">
         <h3>Welcome Back</h3>
     </div>
     <div class="flex center">
         <p>Log into your account</p>
     </div>
     <div>
         <!--The rest of the form here-->
     </div>
</form>
```
If the `asset` method is called without a component name, the asset will be searched for in the document root directory.

### Component Themes

Themes allow you to change the colour scheme of a component without modifying its main styles.

A component's CSS defines its appearance, including layout, spacing, borders, typography and other styling properties. However, instead of hardcoding colours in this CSS, the component uses CSS variables.

These variables are defined by a theme.

#### How it works

Every component can have a **Themes** directory containing one or more theme folders. Each theme folder contains a `Style.css` file that defines the colour variables for that theme.

The component's `Definition.php` determines which theme to use at runtime through the `theme()` method.

For example, consider the following component structure:

```
Button/
├── Definition.php
├── Template.html
├── Style.css
├── Script.js
└── Themes/
    ├── Default/
    │   └── Style.css
    ├── Dark/
    │   └── Style.css
    └── Ocean/
        └── Style.css
```

The `theme()` method in the component's `Definition.php` file returns the name of the theme to apply:

```php
public function theme(Request $request): string {
    return 'Default';
}
```

SaQle uses this name to locate the corresponding theme stylesheet.

#### Defining theme colours

Each theme provides its own set of CSS variables.

For example, the **Default** theme might define:

##### Themes/Default/Style.css

```css
:root {
    --button-background: #2563eb;
    --button-text: #ffffff;
    --button-border: #2563eb;
}
```

The Dark theme could define:

##### Themes/Dark/Style.css

```css
:root {
    --button-background: #1f2937;
    --button-text: #ffffff;
    --button-border: #374151;
}
```

#### Using theme variables

The component's main `Style.css` references these variables instead of defining the actual colours.

```css
.button {
    background-color: var(--button-background);
    color: var(--button-text);
    border: 1px solid var(--button-border);
    padding: 10px 16px;
    border-radius: 6px;
}
```

Notice that the component's main stylesheet does not need to know which theme is being used.

Changing the theme changes the colour scheme, while the component's structure, layout and other styling remain the same.

::: tip
Component themes allow a component to support multiple visual colour schemes while maintaining a single, reusable main stylesheet. To introduce a new colour scheme, you simply add another theme with its own variables. There is no need to rewrite the component's CSS.
:::

### Template Variations

A component typically has a `Template.html` file that defines how its content is presented. This is the component's default template.

However, there are situations where a component needs to present the same content in different ways. For example, a component displaying a collection of products might need to display them as a table, a grid of cards, or a simple list.

Rather than creating separate components for each presentation, SaQle allows a component to have multiple template variations.

Each variation provides an alternative way of rendering the component's content while retaining the same underlying component definition and functionality.

#### How it works

A component can have a **Templates** directory containing subdirectories for its different template variations.

Each subdirectory represents a particular variation and can contain its own template, stylesheet and JavaScript file.

Consider the following component structure:

```
ProductList/
├── Definition.php
├── Template.html
├── Style.css
├── Script.js
└── Templates/
    ├── Table/
    │   ├── Template.html
    │   ├── Style.css
    │   └── Script.js
    ├── Cards/
    │   ├── Template.html
    │   ├── Style.css
    │   └── Script.js
    └── List/
        ├── Template.html
        ├── Style.css
        └── Script.js
```

The root-level `Template.html` is the component's default template. The templates inside the **Templates** directory provide alternative presentations.

#### Selecting a template

The component's `Definition.php` contains a `template()` method that determines which template variation should be used.

For example:

```php
public function template(Request $request): string {
     return 'Cards';
}
```

SaQle uses the returned name to locate the corresponding template inside the component's **Templates** directory.

The selection can be made dynamically at runtime, allowing the component to choose a presentation based on the current context or application requirements.

When no alternative template is selected, the component uses its root-level `Template.html`.

#### Independent styles and scripts

Each template variation can have its own `Style.css` and `Script.js` files.

This allows a particular presentation to have styling and JavaScript behaviour specific to its layout without affecting other variations.

For example:
- The Table variation can define table-specific styling and sorting behaviour.
- The Cards variation can define card layouts and card-specific interactions.
- The List variation can define a compact list layout.

::: tip
Template variations allow a single component to support multiple ways of presenting its content without duplicating the component itself.

The component remains responsible for its definition and underlying functionality, while the selected template determines how its content is rendered.
:::

## The Message Object
