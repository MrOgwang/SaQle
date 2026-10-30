# Project Structure

A SaQle project has a conventional structure that allows the framework to automatically wire and build your application.

By following this conventional structure, you create a project that is easily recognizable, understandable and browsable by you and other developers.

## Root Directory 

Once you have setup the starter saqle project, here are the top level directories.

- **Config** - All the project configuration files go here
- **Env**    - Environmental variables go here. 
- **Public** - Contians the `index.php` file, which is your apps entry point. Also houses your publicly accessible asset files.

	- Public
	- Src
		- Bootstrap
		- Components
		- Databases
			- Migrations
			- Schemas
			- Snapshots
		- Modules
			- Auth (Example module)
				- Components
				- Contracts
				- Data
					- Seeders
				- Listeners
				- Events
				- Middleware
				- Models
				- Notifications
				- Routes
				- Services
				- Validators
	- Storage
	- Vendor
