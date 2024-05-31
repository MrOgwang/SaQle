# SaQle
SaQle is a rapid web app development MVC framework for PHP Developers. This is a fan personal project and by no means meant to compete Laravel or CakePhp and the likes. However, for developers looking to rapidly deploy small to medium scale web apps and/or prototypes, this framework will get you started quickly and get your idea out to the world.

Some Features
1. Integrated CRUD for database interaction
2. MVC architecture
3. Request dispatcher with clean, custom URLs and routes
4. Built-in validation
5. Basic templating with PHP syntax
6. Cookies, security, session, and request handling management components
7. User input sanitization
8. Basic authorization and authentication
9. Multitenancy handling components
10. Email feature with PHPMailer
11. SMS feature with Twilio
12. Image and video uploads resizing and cropping

Installation
1. Download the source code as zip
2. The saqle folder sits outside your project root directory but on the same level as your root directory as follows:
- your_project_root
- saqle
3. While the framework does not impose any particular folder structure for your project, it relies on a set of configurations that must be defined and loaded in index file. A sample config file for a project named Gaso looks like this:
  
`<?php
namespace Gaso\Config;

use Gaso\Apps\Account\Models\{User, Tenant, Role};
use Gaso\Apps\Account\Services\GasoAuthService;
use SaQle\Config\Config;
use Gaso\Session\GasoSessionHandler;
use Gaso\Apps\Account\Notifications\{VerificationCodeEmailSetup, WelcomeEmailSetup};

class GasoConfig extends Config{
	 public function __construct(){
	 	 parent::__construct(...[
	 	 	//'document_root'                 => '',
	 	 	'root_domain'                     => "https://www.gaso.com/",
	 	 	'installed_apps'                  => ['account', 'admin', 'backoffice'],
	 	 	//'primary_key_type'              => '',
	 	 	'auth_model_class'                => User::class,
	 	 	'auth_backend_class'              => GasoAuthService::class,
	 	 	'tenant_model_class'              => Tenant::class,
	 	 	//'media_folder'                  => '',
	 	 	//'api_url_prefixes'              => [],
	 	 	'database_user'                   => 'saqlecom_dbmanager',
	 	 	'database_password'               => 'hxgdM$-j5QWZ',
	 	 	'database_name'                   => 'saqlecom_fuel',
	 	 	//'database_host'                 => '',
	 	 	//'date_added_format'             => '',
	 	 	//'date_display_format'           => '',
	 	 	//'datetime_display_format'       => '',
	 	 	'session_domain'                  => 'www.gaso.com',
	 	 	'session_handler'                 => GasoSessionHandler::class,
	 	 	'email_username'                  => 'gasocloud@gmail.com',
	 	 	'email_password'                  => 'bpxl holi pvxv mqbz',
	 	 	'email_host'                      => 'smtp.gmail.com',
	 	 	'email_port'                      => 587,
	 	 	'email_sender_name'               => 'Gaso Team',
	 	 	'email_sender_address'      	  => 'gasocloud@gmail.com',
	 	 	'welcome_email_setup_class'       => WelcomeEmailSetup::class,
	 	 	'verification_email_setup_class'  => VerificationCodeEmailSetup::class,
	 	 	//'system_admin_email'            => '',
	 	 	//'system_admin_name'             => '',
	 	 	'access_denied_redirect_url'      => 'https://www.gaso.com/error403/',
	 	 	'resource_not_found_redirect_url' => 'https://www.gaso.com/error404/',
	 	 	'role_model_class'                => Role::class
	 	 ]);
		 define("DI_CONTAINER",      DOCUMENT_ROOT."/includes/container.php");
		 define("TWILIO_ACCOUNT_SID", '');
		 define("TWILIO_AUTH_TOKEN", '');
		 define("TWILIO_PHONE_NUMBER", '');
	 }
}

return new GasoConfig();
?>`

    

