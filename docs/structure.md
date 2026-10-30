# Project Structure

A SaQle project has a conventional structure that allows the framework to automatically wire and build your application.

By following this conventional structure, you create a project that is easily recognizable, understandable and browsable by you and other developers.

## Root Directory 

Once you have setup the starter saqle project, here are the top level directories.

- **Config** - Contains all the projection configuration files. 
- **Env**    - Environmental variables go here. 
- **Logs**   - Contains application log files.
- **Public** - Contians the `index.php` file, which is your apps entry point. Also houses your publicly accessible asset files (js, css and images)
- **Src**    - Your application's source code
- **Storage** - Contains compiled templates, cached files, temporary uploads and all other framework generated files.
- **Vendor** - Contains `composer` dependencies.

## The Src/ Directory

This is where you will do most of your development. Here are the top level Src/ directories.

- **Bootstrap** - Contains your `App.php` and `Console.php` files. These are your http and console applications' bootsrapping files respectively.
- **Components** - Contains customized components from the framework library. We will discuss this further in later sections.
- **Databases** - Contains database migraions, snapshots and schemas for your entire project
- **Modules**   - Contains your application modules.

## The Scr/Modules Directory

The `Modules` directory contains all your application modules. A module is a collection of files, assets and resources that contribute to one complete feature of your application. Modules are designed to be self contained and reusable from project to project.

For example: An e-commerce store may have an `Auth`, `Inventory` and `StoreFront` modules.

Here are some conventional directories found in a module.

- **Components**    - Contains application components. See [Components](components.md) for more information.
- **Contracts**     - Contains request contracts. See [Contracts](contracts.md) for more information.
- **Commands**      - Contains console commands. See [Commands](commands.md) for more information.
- **Data**          - Contains database seeders. See [Seeding](seeders.md) for more information.
- **Listeners**     - Contains event listeners. See [Listners](listeners.md) for more information.
- **Events**        - Contains event definitions. See [Events](events.md) for more information.
- **Middleware**    - Contains middleware definitions. See [Middleware](middleware.md) for more information.
- **Models**        - Contains ORM model definitions. See [Models](models.md) for more information.
- **Notifications** - Contains mail and other forms of notifications. See [Emails](emails.md) for more information.
- **Routes**        - Contains application routes. See [Routes](routes.md) for more information.
- **Services**      - Contains business logic services. See [Services](services.md) for more information.
- **Validators**    - Contains input validationb definitions. See [Validator](validators.md) for more information.
