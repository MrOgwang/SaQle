# Getting Started

The best way to get started fast with SaQle is to install the accompanying starter project hosted on github.

It is important that you install this project via composer

## Requirements

- A web server like Apache or Nginx installed on your system
- Composer installed on your system
- PHP 8.4 and above installed on your system
- SaQle currently only supports MySQL

## Get starter project

Inside the directory where you want to create a new application, run the following command in your terminal to download the starter project. Replace my_project with the actual name of your project

```
composer create-project mrogwang/saqle-project my_project
```

## Update environment variables

Once the installation is complete, cd into your new project's root dir.

Open the `env/development/.env` file and update your environment variables to reflect your setup

```
APP_NAME="Your Application Name"

COOKIE_DOMAIN=your-domain.local
ROOT_DOMAIN=your-domain.local

DB_HOST=localhost
DB_USER_NAME=your-db-user
DB_PASSWORD=your-db-password
DB_NAME=your-db-name

EMAIL_USER_NAME=your-email-user
EMAIL_PASSWORD=your-email-password
EMAIL_HOST=your-email-host
EMAIL_PORT=your-email-port
EMAIL_SENDER_ADDRESS=your-email-address

DISPLAY_ERRORS=1
DISPLAY_STARTUP_ERRORS=1

JWT_KEY=your-jwt-key
JWT_TTL=3600

MEDIA_ENCRYPT_KEY=your-media-encryption-key
MEDIA_ENCRYPT_SALT=your-media-encryption-salt
```

## Make migrations

While still insde the project root, run the following command to make Initial migrations. 'Initial' here is the name of this migration instance.

`php saqle make:migrations Initial`

Your project's migrations are created in: `src/Databases/Migrations`

Your project's database snapshots are created in: `src/Databases/Snapshots`

We will discuss these much more later

## Migrate

While still inside the project root, run the following command to set up your physical database and tables. We are assuming you have your database server running.

`php saqle migrate`

If the migration runs successfully, you should have a database with your project's name and some tables that come with the starter project created in it

## Make Super User

SaQle comes with an in built administrator panel that can help you start managing data immediately.

To log into this panel, you will need to create a super user account

Run the following command to create a super user

`php saqle make:superuser`

This command runs interactively. Provide all the information required.

Once the command runs, you should have a super user account created with the user name and password you provided. 

You can log into the admin panel using the url: `your-project-domain/saqle/signin`

We will discuss the admin panels further in later sections

## Open app in browser

If the steps above completed without any problems, your application will be available at: `http://your-domain`

Example: `http://localhost`

Congratulations! You have successfully setup your first project with SaQle.

