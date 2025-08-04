
# Routing

Routing is the mechanism by which you direct a request to a given controller, a controller and method or a view

## Defining routes

Your routes can be defined in two places:  

1. Project level routes are defined in the root directory of your project. Create a folder named _routes_, add a _web.php_ file and a _api.php_ file.
2. App level routes are defined per app. Inside your app folder, create a folder named _routes_, add a _web.php_ file and a _api.php_ file.

#### _Convention_

The _api.php_ file will contain your api routes. This means any routes defined here will handle api and ajax requests and will return a JSON response by default with an approrpiate response header.

The _web.php_ file will contain routes for regular web requests that return a html view or a web resource. 

#### _Configuration_

In the project configuration file **_app.config.php_** at **_your_project_dir/config_** folder, the following configurations are available for routes:

1. _api_url_prefixes_: a string array of the prefixes to be attached to your api endpoints
2. _sse_url_prefixes_: a string array of the prefixes to be attached to your sse endpoints

```
<?php
namespace YourAppName\Config;

//one or more use statements here

return [
     //more config keys here
     
     //api url prefixes
     "api_url_prefixes" => ['/api/v1/', '/api/v2/'],
     
     //sse url prefixes
     "sse_url_prefixes" => ['/sse/v1/', '/sse/v2/'],
 ]
?>
``` 
#### Routing classes

SaQle routing is supported by two classes: **_Router_** and **_Route_** class defined in **_SaQle/Routes_** namespace.

#### 1. Router

The _Router_ class in _SaQle/Routes_ exposes static methods you can use to define your routes.

The static methods available are: **post, get, patch, put, delete, match**. These methods all return a **_Route_** object. 

We will look further into each of these methods.

##### post

Use the _post_ method of the _Router_ class to define a route that handles a http method **POST** request. The post method is defined as follows:

```
     static public function post(string $url, string | array $target, ?string $target_method = null) : Route 
```

##### get 

Use the _get_ method of the _Router_ class to define a route that handles a http method **GET** request. The get method is defined as follows:

```
static public function get(string $url, string | array $target, ?string $target_method = null) : Route 
```

##### put

Use the _put_ method of the _Router_ class to define a route that handles a http method **PUT** request. The put method is defined as follows:

```
     static public function put(string $url, string | array $target, ?string $target_method = null) : Route 
```

##### patch

Use the _patch_ method of the _Router_ class to define a route that handles a http method **PATCH** request. The patch method is defined as follows:

```
     static public function patch(string $url, string | array $target, ?string $target_method = null) : Route 
```

##### delete

Use the _delete_ method of the _Router_ class to define a route that handles a http method **DELETE** request. The delete method is defined as follows:

```
     static public function delete(string $url, string | array $target, ?string $target_method = null) : Route 
```

##### match

Use the _match_ method of the _Router_ class to define a route that handles multiple http methods. The match method is defined as follows:

```
     static public function match(array $methods, string $url, string | array $target) : Route 
```

**_post_**, **_put_**, **_patch_**, **_delete_** and **_get_** methods all accept the arguments described below:

_url_**(string)**: the uniform resource locator to match for a route  
_target_**(string|array)**: 
 1. this is the name of the controller class or the name of the view file if provided as a string.
 2. if this is provided as an array, the array must be a key => value array  
 where the key is a user role and the value is a controller class name or view name

 _target_method_**(nullable string)**: this is the name of the method on the controller to call. Only provided if the target is a controller.  
 
 Where this is not provided for a controller, the $target_method defaults to the name of the http method in all lowercase.

**_match_** method accepts the following arguments

_methods_**(array)**: a key => value array of the http methods to match  
where they key is the http method name and the value is the controller method name.

_url_**(string)**: the uniform resource locator to match for a route  

_target_**(string)**: this is the name of the controller class or the name of the view file for web routes

#### 2. Route

You will rarely interact with the **_Route_** object directly, except when using its exposed public methods during route definiton.

The methods of the **_Route_** object you will use include: **_with_parents_** and **_with_default_**.

##### with_parents

When defining web routes, this method is used to describe a layout structure for your templates.

```
public function with_parents(array $parents) : void 
```
The method accepts an _array_ **_$parents_** argument, which is a an array of strings that
reperesnt the names of controller classes or the view file names.

##### with_default

When defining the web routes, this method is used to set the default template to be used when a parent template is requested.

```
public function with_default(string $controller) : void
```

This method accepts a _string_ **_$controller_** argument, which the controller class name or the name of the view file to use as default.

We will see how to use _with_parents_ and _with_default_ methods below when we discuss web routes.

##### with_permissions

When defining routes, this method is used to describe the permissions required for a user to access the said route.

```
 public function with_permissions(array $permissions) : void
```

The method accepts an _array_ **_$permissions_** argument, which is a an array of strings that
reperesnt the names of permissions to evaluate.

We will see how to apply roles and permissions to routes in the **Roles And Permissions** section.

## API routes

Api routes are simple. A request is directed to a given controller or controller with an explicit method. The controller method is executed to get the response data which is returned to the client as JSON. 

Note that API requests only hit one controller.

Example project level _api_.php file:

```
<?php
declare(strict_types = 1);

namespace YourAppName\Routes;

use SaQle\Routes\Router;
use YourAppName\Apps\Account\Controllers\{Signin, Signup}; //require your controllers

//the framework will not attach the api url prefix here
Router::post("/api/v1/signup/", Signup::class, 'create_account');

//the framework attaches an api url prefix here as defined in the configuration file.
//the first prefix is picked by default
Router::post("/signin/", Signin::class, 'login');

//the framework attaches an api url prefix here as defined in the configuration file.
//the first prefix is picked by default
//because the target method has not been explicitly defined, the framework looks for a method
//named post, and throws an exception if not found
Router::post("/verify-account/", Signup::class);

?>
```

## Web routes

Web routes are a bit complex. In addition to directing requests, web routes are also used to define the layout structure of a web application. It is important to note that the inheritance mechanism for the framework's templates is defined in the web routes. Lets understand this with an example:

I am developing a **school portal**, with **teacher**, **student** and **support staff** modules. My portal is home page is as follows:

_home.html_

```
@css portal @endcss
@js portal @endjs

<div class='home'>
    <div class='home_header'>
        <a href='/school/portal/teachers/'>Teachers</a>
        <a href='/school/portal/students/'>Students</a>
        <a href='/school/portal/support/'>Support Staff</a>
    </div>

    <div class='home_body'>
        <!-- Here is where we expect dynamic content from teachers, students and support stuff views depending on the link that will be clicked-->
        @content @endcontent
    </div>

    <div class='home_footer'>
        <!-- Footer content like links and contacts here-->
    </div>
</div>

```

__teacher.html_

```
<div class='teachers'>
    <div class='teachers_header'>
        <a href='/school/portal/teachers/workhours/'>Work Hours</a>
        <a href='/school/portal/teachers/salaries/'>Salaries</a>
        <a href='/school/portal/teachers/timetable/'>Timetable</a>
    </div>
    <div class='teachers_body'>
        @content @endcontent
    </div>
</div>
```

__workhours.html_

```
<div class='workhours'>
   <ul>
       <li>Monday: 8:00 AM - 4:30 PM</li>
       <li>Tuesday: 9:00 AM - 3:30 PM</li>
       <li>Wednesday: 10:00 AM - 5:30 PM</li>
       <li>Thursday: 7:00 AM - 3:30 PM</li>
       <li>Friday: 11:00 AM - 2:00 PM</li>
   </ul>
</div>
```
When a user visits the work hours link, I want to return a complete view composed of the home template, the teacher template and the workhours template.

How do I use web routes to define the layout structure I need?

_web.php_

```
<?php
declare(strict_types = 1);

namespace SchoolPortal\Routes;

use SaQle\Routes\Router;

//require the controllers
use SchoolPortal\Controllers\{Home, Teachers, Students, SupportStaff, Workhours};

/**
* When request for home is made, the students template will be loaded
* into the home template and the complete view is returned
*/
Router::get("/", Home::class)->with_default(Students::class);

/**
* When a request for teachers is made, the workhours template is loaded
into the teachers template, which is loaded into the home template and the full
view is retruned.
*/
Router::get("/school/portal/teachers/", Teachers::class)  
->with_parents([Home::class])->with_default(Workhours::class);

Router::get("/school/portal/students/", Students::class)->with_parents([Home::class]);
Router::get("/school/portal/support/", SupportStaff::class)->with_parents([Home::class]);

/**
* When a request for work hours is made,  
the workhours template is loaded into teachers template  
which is loaded into home template and the full view is returned
*/
Router::get("/school/portal/teachers/workhours/", Workhours::class)->with_parents([Home::class, Teachers::class]);

?>
```

If you have many templates that need to inherit from the same parent, use the **_from_parents_** method of the **_Router_** class to group these routes under the same parent.

```
Router::from_parents([Home::class], [
    Router::get("/school/portal/teachers/", Teachers::class)->with_default(Workhours::class),  
    Router::get("/school/portal/students/", Students::class),
    Router::get("/school/portal/support/", SupportStaff::class)
]);
```

## Roles And Permissions

Roles and permissions can be defined in two places:
 1. **Controller** classes, which we will see later and
 2. **Routes** 

 ### Defining roles and permissions in routes

 To use **roles and permissions** in routes and subsequently anywhere else, all your roles and permissions must first be defined in the **AuthorizationProvider** class.

 #### Roles

 There are instances where for the same route you want to specify a different target(Controller class or view name) depending on the role of the user. 

 This can be achieved by passing a **_key => value_** _array_ in place of a _string_ for the **_target_** parameter.

 The _key_ will be a string name for a role, and the value will be the string controller class name or the name of the view file.

 ```
static public function get(string $url, string | array $target, ?string $target_method = null) : Route 
```
_web.php_

```
//require controllers
use SchoolPortal\Controllers\{Teachers, Students, SupportStaff, Dashboard};

Router::get("/portal/", [
     'teacher' => Teachers::class,
     'student' => Students::class,
     'support' => SupportStaff::class
])->with_default(Dashboard::class);
```

#### The ProxyController

Sometimes, the logic to decide which controller to return for a given route is more complicated than a simple key => value array. In such cases the framework provides the _**ProxyController**_ class in **_SaQle\Controllers_** namespace. 

This is an abstact class with two abstract methods _**destination**_ and _**get_actions**_ to be overriden by the calling client.

```
<?php
namespace SaQle\Controllers;

use SaQle\Controllers\Base\BaseController;

//other use statements here

abstract class ProxyController extends BaseController{
     //other class code here
     
     abstract protected function destination() : BaseController;
     
     abstract public function get_actions() : null | array;
}
```
##### destination

Returns a string representing a controller name or view file name. Override to impelement own logic.

##### get_actions

Returns an array of **_target_methods_** to associate with the controllers mentioned in the destination method. This is a key => value array where the key is the controller or view file name and the value is the target method.

##### Using a proxy controller in routes

1. Define your proxy controller class anywhere in the project. This class must inherit
from the abstract **ProxyController** class in **_SaQle\Controllers\ProxyController_** namespace.

```
<?php
namespace SchoolPortal\Controllers;

use SaQle\Controllers\ProxyController;
use SaQle\Controllers\Base\BaseController;
use SaQle\Auth\Permissions\Guard;

//require your controllers
use SchoolPortal\Controllers\{Teachers, Students, SupportStaff, Dashboard};

class PortalHome extends ProxyController {
     private function is_teacher_allowed() : bool {
         //some complicated logic that returns true or false
         return true;
     }
     private function is_student_allowed() : bool {
         //some complicated logic that returns true or false
         return true;
     }
     private function is_support_allowed() : bool {
         //some complicated logic that returns true or false
         return true;
     }
     protected function destination() : BaseController {
         if(Guard::check('is_teacher') && $this->is_teacher_allowed())
             return new Teachers();
            
         if(Guard::check('is_student') && $this->is_student_allowed())
             return new Students();

         if(Guard::check('is_support') && $this->is_support_allowed())
             return new SupportStaff();
     }

     public function get_actions() : null | array {
         return match($this->controller::class){
             Teachers::class     => null,
             Students::class     => null,
             SupportStaff::class => null,
         };
     }
}
?>
```

2. Use the proxy controller class in your routes file.

```
//require controllers
use SchoolPortal\Controllers\{PortalHome, Dashboard};

Router::get("/portal/", PortalHome::class)->with_default(Dashboard::class);
```

#### Permissions

Use the **_with_permissions_** method of the **_Route_** class to specify permissions required to access a route.

```
//require controllers
use SchoolPortal\Controllers\{PortalHome, Dashboard};

Router::get("/portal/", PortalHome::class)  
->with_permissions(['can_view_dashboard'])  
->with_default(Dashboard::class);
```



