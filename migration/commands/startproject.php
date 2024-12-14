<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class StartProject{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($name){
           $this->manager->start_project(...[
                'name'     => $name, 
                'index'    => $this->get_index_file_contents($name),
                'htaccess' => $this->get_htaccess_file_contents($name),
                'manager'   => $this->get_manage_file_contents($name),
                'config'   => $this->get_config_file_contents($name),
                'dbseed'  => $this->get_seeding_file_contents($name),
                'web' => $this->get_webroutes_file_contents($name),
                'api' => $this->get_apiroutes_file_contents($name),
                'session' => $this->get_sessionhandler_file_contents($name),
                'dbcontext' => $this->get_dbcontext_file_contents($name),
                'signin'    => $this->get_signin_file_contents($name),
                'signout'   => $this->get_signout_file_contents($name),
                'usermodel' => $this->get_usermodel_file_contents($name),
                'userschema' => $this->get_userschema_file_contents($name),
                'usercollection' => $this->get_usercollection_file_contents($name),
                'welcomeemailsetup' => $this->get_welcomeemailsetup_file_contents($name),
                'welcomeemail' => $this->get_welcomeemail_file_contents($name),
                'accapiroutes' => $this->get_accapiroutes_file_contents($name),
                'accwebroutes' => $this->get_accwebroutes_file_contents($name),
                'accountservice' => $this->get_accountservice_file_contents($name),
                'authservice'    => $this->get_authservice_file_contents($name),
                'signinhtml' => $this->get_signinhtml_file_contents($name),
                'homehtml' => $this->get_homehtml_file_contents($name),
                'homecontroller' => $this->get_homecontroller_file_contents($name),
                'dirmanager' => $this->get_dirmanager_file_contents($name),
                'showfile' => $this->get_showfile_file_contents($name),
                'isbackoffice' => $this->get_isbackoffice_file_contents($name)
           ]);
     }

     private function get_isbackoffice_file_contents($name){
           $namespace = ucfirst($name);

           $content = "\n<?php";
           $content .= "\nnamespace {$namespace}\Permissions;";
           $content .= "\n";
           $content .= "\nuse SaQle\Permissions\Permission;";
           $content .= "\n";
           $content .= "\nclass IsBackoffice extends Permission{";
           $content .= "\n\tpublic function has_permission() : bool{";
           $content .= "\n\t\t$"."user = $"."this->request->session->get('user', '');";
           $content .= "\n\t\treturn $"."user && $"."user->label === 'BACKOFFICE';";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";
           return $content;
     }

     private function get_showfile_file_contents($name){
           $content = "";
           $content .= "\n<?php";
           $content .= "\nrequire_once dirname($"."_SERVER['DOCUMENT_ROOT']).'/saqle/commons/stringutils.php';";
           $content .= "\nuse SaQle\Commons\StringUtils;";
           $content .= "\nclass ShowFile{";
           $content .= "\n\tuse StringUtils;";
           $content .= "\n\tpublic function __construct(){";
           $content .= "\n\t\t$"."file_name = basename($"."_GET['file']);";
           $content .= "\n\t\t$"."file_path = $"."this->decrypt($"."_GET['xyz'], $"."file_name);";
           $content .= "\n\t\t$"."abs_path  = $"."file_path.$"."file_name;";
           $content .= "\n\t\tif (file_exists($"."abs_path)){";
           $content .= "\n\t\t\theader('Content-Description: File Transfer');";
           $content .= "\n\t\t\theader('Content-Type: application/octet-stream');";
           $content .= "\n\t\t\theader('Content-Disposition: attachment; filename='.$"."file_name);";
           $content .= "\n\t\t\theader('Content-Transfer-Encoding: binary');";
           $content .= "\n\t\t\theader('Expires: 0');";
           $content .= "\n\t\t\theader('Cache-Control: must-revalidate, post-check=0, pre-check=0');";
           $content .= "\n\t\t\theader('Pragma: public');";
           $content .= "\n\t\t\theader('Content-Length: '. filesize($"."abs_path));";
           $content .= "\n\t\t\tob_clean();";
           $content .= "\n\t\t\tflush();";
           $content .= "\n\t\t\treadfile($"."abs_path);";
           $content .= "\n\t\t\texit;";
           $content .= "\n\t\t}";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n$"."showfile = new ShowFile();";
           $content .= "\n?>";

           return $content;
     }

     private function get_dirmanager_file_contents($name){
           $namespace = ucfirst($name);
 
           $content = "<?php";
           $content .= "\nnamespace {$namespace}\DirManager;";
           $content .= "\n";
           $content .= "\nuse SaQle\DirManager\DirManager;";
           $content .= "\n";
           $content .= "\nclass {$namespace}DirManager extends DirManager{";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }

     private function get_homecontroller_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Controllers;";
           $content .= "\n";
           $content .= "\nuse SaQle\Controllers\IController;";
           $content .= "\nuse SaQle\Http\Response\{HttpMessage, StatusCode};";
           $content .= "\n";
           $content .= "\nclass Home extends IController{";
           $content .= "\n\tpublic function post() : HttpMessage{";
           $content .= "\n\t\treturn new HttpMessage(StatusCode::OK, $"."this->get()->get_response());";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function get() : HttpMessage{";
           $content .= "\n\t\treturn new HttpMessage(StatusCode::OK, ['sitename' => '".$name."']);";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }

     private function get_homehtml_file_contents($name){
           $content = "@css samplehome @endcss";
           $content .= "\n";
           $content .= "\n<div class='sample-homepage'>";
           $content .= "\n\t<div class='sample-homepage-top'>";
           $content .= "\n\t\t<a href='{{ $"."base_url }}'>Home</a>";
           $content .= "\n\t\t@if(!$"."user)";
           $content .= "\n\t\t<a href='{{ $"."base_url }}/createaccount/'>Create Account</a>";
           $content .= "\n\t\t<a href='{{ $"."base_url }}/login/'>Log in</a>";
           $content .= "\n\t\t<a href='{{ $"."base_url }}/logout/'>Log out</a>";
           $content .= "\n\t\t@else";
           $content .= "\n\t\t<h4>{{ $"."user->first_name }} is loggedin</h4>";
           $content .= "\n\t\t@endif";
           $content .= "\n\t</div>";
           $content .= "\n\t<div class='sample-homepage-middle'>";
           $content .= "\n\t<div>";
           $content .= "\n\t\t\t<h1>Hey, welcome to {{ $"."sitename }}</h1>";
           $content .= "\n\t\t\t<p>This app is built using the SaQle framework. If you can see this page it means you have set up things correctly";
           $content .= "\n\t\t\tand you can start development. </p>";
           $content .= "\n\t\t\t<p>Happy Coding!</p>";
           $content .= "\n\t\t</div>";
           $content .= "\n\t</div>";
           $content .= "\n\t<div class='sample-homepage-bottom'>";
           $content .= "\n\t\t<p>Have any questions? Reach out to the library author at wycliffomondiotieno@gmail.com. ";
           $content .= "\n\t\tA cup of tea will be appreciated</p>";
           $content .= "\n\t</div>";
           $content .= "\n</div>";

           return $content;
     }

     private function get_signinhtml_file_contents($name){
           $content = "@css samplesignin @endcss";
           $content .= "\n<form method='POST' class='sample-signin'>";
           $content .= "\n\t<div>";
           $content .= "\n\t\t<h2>Welcome Back</h2>";
           $content .= "\n\t\t<p>Log in to proceed</p>";
           $content .= "\n\t\t<div>";
           $content .= "\n\t\t\t<label for='username'>User Name</label>";
           $content .= "\n\t\t\t<input type='text' name='username' id='username'>";
           $content .= "\n\t\t</div>";
           $content .= "\n\t\t<div>";
           $content .= "\n\t\t\t<label for='password'>Password</label>";
           $content .= "\n\t\t\t<input type='password' name='password' id='password'>";
           $content .= "\n\t\t</div>";
           $content .= "\n\t\t<div>";
           $content .= "\n\t\t\t<button type='submit'>Log In</button>";
           $content .= "\n\t\t</div>";
           $content .= "\n\t</div>";
           $content .= "\n</form>";
           
           return $content;
     }

     private function get_accwebroutes_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\ndeclare(strict_types = 1);";
           $content .= "\n";
           $content .= "\nnamespace {$namespace}\Apps\Account\Routes;";
           $content .= "\n";
           $content .= "\nuse SaQle\Routes\Route;";
           $content .= "\nuse {$namespace}\Apps\Account\Controllers\Signin";
           $content .= "\n";
           $content .= "\nreturn [";
           $content .= "\n\tnew Route(['POST', 'GET'], '/', Signin::class),";
           $content .= "\n];";
           $content .= "?>";

           return $content;
     }

     private function get_accapiroutes_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\ndeclare(strict_types = 1);";
           $content .= "\n";
           $content .= "\nnamespace {$namespace}\Apps\Account\Routes;";
           $content .= "\n";
           $content .= "\nuse SaQle\Routes\Route;";
           $content .= "\n";
           $content .= "\nreturn [";
           $content .= "\n";
           $content .= "\n];";
           $content .= "?>";

           return $content;
     }

     private function get_authservice_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Apps\Account\Services;";
           $content .= "\n";
           $content .= "\nuse SaQle\Auth\Services\AuthService;";
           $content .= "\nuse {$namespace}\Apps\Account\Data\AccountsDbContext;";
           $content .= "\nuse SaQle\Http\Request\Request;";
           $content .= "\nuse SaQle\FeedBack\FeedBack;";
           $content .= "\n";
           $content .= "\nclass BooiboAuthService extends AuthService{";
           $content .= "\n\tpublic function __construct(Request $"."request){";
           $content .= "\n\t\tparent::__construct($"."request, AccountsDbContext::class);";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function authenticate(){";
           $content .= "\n\t\t$"."clearpass = $"."this->request->data->get('password', '');";
           $content .= "\n\t\tif(!$"."clearpass){";
           $content .= "\n\t\t\t$"."clearpass = $"."this->request->data->get('enteruserpassword');";
           $content .= "\n\t\t}";
           $content .= "\n";
           $content .= "\n\t\t$"."username = $"."this->request->data->get('email_address', '');";
           $content .= "\n\t\tif(!$"."username){";
           $content .= "\n\t\t\t$"."username = $"."this->request->data->get('enterusername');";
           $content .= "\n\t\t}";
           $content .= "\n\t\t$"."password = md5($"."clearpass);";
           $content .= "\n";
           $content .= "\n\t\t$"."user = $"."this->context->users";
           $content .= "\n\t\t->where('password__eq', $"."password)";
           $content .= "\n\t\t->where('username__eq', $"."username)";
           $content .= "\n\t\t->limit(1, 1)";
           $content .= "\n\t\t->tomodel()";
           $content .= "\n\t\t->first_or_default();";
           $content .= "\n\t\t//Is user available";
           $content .= "\n\t\tif(!$"."user) return $"."this->feedback->get(status: FeedBack::INVALID_INPUT, message: 'Invalid Credentials!');";
           $content .= "\n";
           $content .= "\n\t\t//Is user account disabled";
           $content .= "\n\t\tif($"."user->disabled === 1) return $"."this->feedback->get(status: FeedBack::INVALID_INPUT, message: 'Your account has been disabled!');";
           $content .= "\n";
           $content .= "\n\t\t//Is user account deleted";
           $content .= "\n\t\tif($"."user->deleted === 1) return $"."this->feedback->get(status: FeedBack::INVALID_INPUT, message: 'This account does not exist!'');";
           $content .= "\n";
           $content .= "\n\t\t$"."user_info = ['user' => $"."user];";
           $content .= "\n";
           $content .= "\n\t\t//Set feedback status and notify observers";
           $content .= "\n\t\t$"."this->feedback->set(FeedBack::SUCCESS, $"."user_info);";
           $content .= "\n\t\t$"."this->notify();";
           $content .= "\n";
           $content .= "\n\t\t//Return successful feedback";
           $content .= "\n\t\treturn $"."this->feedback->get_feedback();";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }

     private function get_usermodel_file_contents($name){
           $content = "<?php";
           $content .= "\n//Run migrations to generate user model";
           $content .= "\n?>";
           return $content;
     }

     private function get_usercollection_file_contents($name){
           $content = "<?php";
           $content .= "\n//Run migrations to generate user collection!";
           $content .= "\n?>";
           return $content;
     }

     private function get_accountservice_file_contents($name){
           $content = "<?php";
           $content .= "?>";
           return $content;
     }


     private function get_welcomeemailsetup_file_contents($name){
           $content = "<?php";
           $content .= "?>";
           return $content;
     }

     private function get_welcomeemail_file_contents($name){
           $content = "<?php";
           $content .= "?>";
           return $content;
     }

     private function get_userschema_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Apps\Account\Models\Schema;";
           $content .= "\n";
           $content .= "\nuse SaQle\Auth\Models\Schema\BaseUserSchema;";
           $content .= "\nuse SaQle\Dao\Field\Types\{TextField, IntegerField, FileField, OneToOne, OneToMany, ManyToMany, DateField, BooleanField};";
           $content .= "\nuse SaQle\Dao\Field\Interfaces\IField;";
           $content .= "\nuse {$namespace}\DirManager\BooiboDirManager;";
           $content .= "\n";
           $content .= "\nclass UserSchema extends BaseUserSchema{";
           $content .= "\n\tpublic IField $"."middle_name;";
           $content .= "\n\tpublic IField $"."other_names;";
           $content .= "\n\tpublic IField $"."dob;";
           $content .= "\n\tpublic IField $"."gender;";
           $content .= "\n\tpublic IField $"."profilephoto;";
           $content .= "\n\tpublic IField $"."is_online;";
           $content .= "\n\tpublic IField $"."account_status;";
           $content .= "\n\tpublic IField $"."disabled;";
           $content .= "\n";
           $content .= "\n\tpublic function __construct(...$"."kwargs){";
           $content .= "\n\t\t$"."this->middle_name = new TextField(required: false, strict: false);";
           $content .= "\n\t\t$"."this->other_names = new TextField(required: false, strict: false);";
           $content .= "\n\t\t$"."this->dob = new DateField(required: true, strict: false);";
           $content .= "\n\t\t$"."this->gender = new TextField(required: true, strict: false, choices: ['male', 'female']);";
           $content .= "\n\t\t$"."this->profilephoto = new FileField(required: false, max: 10, accept: ['image/*'], ";
           $content .= "\n\t\tpath: 'path(user_id)', rename: 'rename(user_id)', dpath: 'get_default_photo(gender)', ";
           $content .= "\n\t\tshow: 'show_file', crop: [40, 80, 160, 200], resize: [40, 80, 160, 200]);";
           $content .= "\n\t\t$"."this->is_online = new BooleanField(required: false);";
           $content .= "\n\t\t$"."this->account_status = new IntegerField(required: false, zero: true, absolute: true, choices: [";
           $content .= "\n\t\t\t'0' => 'Temporary', ";
           $content .= "\n\t\t\t'1' => 'Onboarding',";
           $content .= "\n\t\t\t'2' => 'Onboarded',";
           $content .= "\n\t\t\t'3' => 'Active'";
           $content .= "\n\t\t], usekeys: true);";
           $content .= "\n\t\t$"."this->disabled = new BooleanField(required: false, choices: ['0' => 'Disable Account', '1' => 'Enable Account'], usekeys: true);";
           $content .= "\n\t\tparent::__construct(...$"."kwargs);";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function rename($"."user, $"."file_name, $"."file_index = 0){";
           $content .= "\n\t\t$"."file_name_parts = explode(".", $"."file_name);";
           $content .= "\n\t\t$"."extension = end($"."file_name_parts);";
           $content .= "\n\t\treturn 'userProfilePicture-{"."$"."user->user_id}.{"."$"."extension}';";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function path($"."user){";
           $content .= "\n\t\treturn  (new {$namespace}DirManager())->get_user_personal_dir('', $"."user->user_id, 'images');";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function get_default_photo($"."user){";
           $content .= "\n\t\treturn $"."user->gender == 'male' ? '/media/profile/male.jpg' : '/media/profile/female.jpg';";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function show_file($"."user){";
           $content .= "\n\t\treturn '/dirmanager/showfile.php';";
           $content .= "\n\t\}";
           $content .= "\n}";
           $content .= "?>";

           return $content;
     }

     private function get_signout_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Apps\Account\Controllers;";
           $content .= "\n";
           $content .= "\nuse SaQle\Controllers\IController;";
           $content .= "\nuse SaQle\Http\Response\{HttpMessage, StatusCode};";
           $content .= "\nuse SaQle\Permissions\IsAuthenticated;";
           $content .= "\n";
           $content .= "\nclass Signout extends IController{";
           $content .= "\n\tprotected array $"."permissions = [IsAuthenticated::class];";
           $content .= "\n";
           $content .= "\n\tpublic function get() : HttpMessage{";
           $content .= "\n\t\tsession_start();";
           $content .= "\n\t\tsession_destroy();";
           $content .= "\n\t\theader('Location: '.ROOT_DOMAIN);";
           $content .= "\n\t\tdie();";
           $content .= "\n\t\treturn new HttpMessage(StatusCode::OK);";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }

     private function get_signin_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Apps\Account\Controllers;";
           $content .= "\n";
           $content .= "\nuse SaQle\Controllers\IController;";
           $content .= "\nuse {$namespace}\Apps\Account\Services\{$namespace}AuthService;";
           $content .= "\nuse SaQle\Auth\Observers\SigninObserver;";
           $content .= "\nuse SaQle\Http\Response\{HttpMessage, StatusCode};";
           $content .= "\n";
           $content .= "\nclass Signin extends IController{";
           $content .= "\n\tpublic function post() : HttpMessage{";
           $content .= "\n\t\t$"."service = new {$namespace}AuthService($"."this->request);";
           $content .= "\n\t\tnew SigninObserver($"."service, $"."this->request->data->get('redirect_to', ''));";
           $content .= "\n\t\t$"."feedback = $"."service->authenticate();";
           $content .= "\n\t\t$"."context = $"."this->get()->get_response();";
           $content .= "\n\t\t$"."context['status'] = $"."feedback['status'];";
           $content .= "\n\t\t$"."context['message'] = $"."feedback['message'];";
           $content .= "\n\t\treturn new HttpMessage(StatusCode::OK, $"."context);";
           $content .= "\n\t}";
           $content .= "\n";
           $content .= "\n\tpublic function get() : HttpMessage{";
           $content .= "\n\t\treturn new HttpMessage(StatusCode::OK, [";
           $content .= "\n\t\t\t'message'       => '', ";
           $content .= "\n\t\t\t'request_url'   => '', ";
           $content .= "\n\t\t\t'enterusername' => '',";
           $content .= "\n\t\t\t'status'        => 0,";
           $content .= "\n\t\t]);";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }

     private function get_dbcontext_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\ndeclare(strict_types = 1);";
           $content .= "\nnamespace {$namespace}\Apps\Account\Data;";
           $content .= "\n";
           $content .= "\nuse SaQle\Dao\DbContext\SetupDbContext;";
           $content .= "\n";
           $content .= "\nclass AccountsDbContext extends SetupDbContext{";
           $content .= "\n\tstatic public function get_models(){";
           $content .= "\n\t\t$"."models = [";
           $content .= "\n\t\t\t//'tablename' => TableSchema::class,";
           $content .= "\n\t\t];";
           $content .= "\n\t\treturn array_merge(parent::get_models(), $"."models);";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }
     
     private function get_index_file_contents($name){
           $namespace = ucfirst($name);

           $index = "\n<?php";
           $index .= "\n//require the autoloader for saqle";
           $index .= "\nrequire_once dirname($"."_SERVER['DOCUMENT_ROOT']).'/saqle/autoloader.php';";
           $index .= "\nSaQle\Autoloader::register();";
           $index .= "\n";
           $index .= "\nuse SaQle\App;";
           $index .= "\n";
           $index .= "\n//create new app";
           $index .= "\n$"."app = new App();";
           $index .= "\n$"."app::environment('development');";
           $index .= "\n";
           $index .= "\n//register autoloader for app";
           $index .= "\n$"."app::autoloader()::register(function($"."class){";
           $index .= "\n\tif(str_contains($"."class, '".$namespace."')){";
           $index .= "\n\t\t$"."class = strtolower($"."class);";
           $index .= "\n\t\t$"."pos   = strpos($"."class, '".$namespace."');";
           $index .= "\n\t\tif($"."pos !== false){";
           $index .= "\n\t\t\t$"."class = substr_replace($"."class, '', $"."pos, strlen('".$namespace."'));";
           $index .= "\n\t\t}";
           $index .= "\n\t\t$"."class = str_replace('\\\\', '/', $"."class);";
           $index .= "\n\t\t$"."file = $"."_SERVER['DOCUMENT_ROOT'].$"."class.'.php';";
           $index .= "\n";
           $index .= "\n\t\tif(file_exists($"."file)){";
           $index .= "\n\t\t\trequire $"."file;";
           $index .= "\n\t\t}";
           $index .= "\n\t}";
           $index .= "\n});";
           $index .= "\n";
           $index .= "\n//register static resources";
           $index .= "\n$"."app::static()::link('css', [";
           $index .= "\n";
           $index .= "\n]);";
           $index .= "\n$"."app::static()::link('js', [";
           $index .= "\n";
           $index .= "\n]);";
           $index .= "\n$"."app::static()::script('');";
           $index .= "\n//register page meta data";
           $index .= "\n$"."app::meta()::tags([";
           $index .= "\n";
           $index .= "\n]);";
           $index .= "\n";
           $index .= "\n//register custom middlewares";
           $index .= "\n$"."app::middleware()::register([";
           $index .= "\n";
           $index .= "\n]);";
           $index .= "\n";
           $index .= "\n//register and load app configurations";
           $index .= "\n$"."app::config()::register(require_once __DIR__.'/config/config.php');";
           $index .= "\n$"."app::config()::directory('config');";
           $index .= "\n$"."app::config()::load();";
           $index .= "\n";
           $index .= "\n//register global context values";
           $index .= "\n$"."app::context()::register([";
           $index .= "\n\t'base_url' => ROOT_DOMAIN,";
           $index .= "\n\t'layout_image_path' => LAYOUT_IMAGE_PATH";
           $index .= "\n]);";
           $index .= "\n";
           $index .= "\n//register some controller references";
           $index .= "\n$"."app::controllers()::register([";
           $index .= "\n";
           $index .= "\n]);";
           $index .= "\n";
           $index .= "\n//run app";
           $index .= "\n$"."app::run();";
           $index .= "\n?>";

           return $index;
     }

     private function get_manage_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\n//require the autoloader for saqle";
           $content .= "\nrequire_once realpath(dirname(__FILE__).'/../saqle/').'/autoloader.php';";
           $content .= "\nSaQle\Autoloader::register(function($"."class){";
           $content .= "\n\tif(str_contains($"."class, 'SaQle')){";
           $content .= "\n\t\t$"."saqle = realpath(dirname(__FILE__).'/../saqle/');";
           $content .= "\n\t\t$"."class = strtolower(str_replace('\\\\', '/', str_replace('saqle', '', strtolower($"."class))));";
           $content .= "\n\t\trequire $"."saqle.$"."class.'.php';";
           $content .= "\n\t}else{";
           $content .= "\n\t\t$"."dirname = strtolower(dirname(__FILE__));";
           $content .= "\n\t\t$"."dirparts = explode(DIRECTORY_SEPARATOR, $"."dirname);";
           $content .= "\n\t\t$"."rootfolder = end($"."dirparts);";
           $content .= "\n\t\t$"."class = strtolower($"."class);";
           $content .= "\n\t\t$"."pos   = strpos($"."class, $"."rootfolder);";
           $content .= "\n\t\tif($"."pos !== false){";
           $content .= "\n\t\t\t$"."class = substr_replace($"."class, '', $"."pos, strlen($"."rootfolder));";
           $content .= "\n\t\t}";
           $content .= "\n\t\t$"."class = str_replace('\\\\', '/', $"."class);";
           $content .= "\n\t\trequire dirname(__FILE__).$"."class.'.php';";
           $content .= "\n\t}";
           $content .= "\n});";
           $content .= "\n";
           $content .= "\nuse SaQle\App;";
           $content .= "\nuse SaQle\Manage\Manage;";
           $content .= "\n";
           $content .= "\n//create new app";
           $content .= "\n$"."app = new App();";
           $content .= "\n";
           $content .= "\n//register and load app configurations";
           $content .= "\n$"."app::config()::register(require_once __DIR__.'/config/config.php');";
           $content .= "\n$"."app::config()::directory('config');";
           $content .= "\n$"."app::config()::load();";
           $content .= "\n";
           $content .= "\n$"."argv[] = dirname(__FILE__);";
           $content .= "\n(new Manage($"."argv))();";
           $content .= "\n?>";

           return $content;
     }

     private function get_htaccess_file_contents($name){
           $content = "RewriteEngine On";
           $content .= "\nRewriteCond %{REQUEST_FILENAME} !-f";
           $content .= "\nRewriteCond %{REQUEST_FILENAME} !-d";
           $content .= "\nRewriteRule ^(.*)$ index.php/$1 [L]";

           return $content;
     }

     private function get_config_file_contents($name){
           $namespace = ucfirst($name);
           $domain    = strtolower($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Config;";
           $content .= "\n";
           $content .= "\nuse {$namespace}\Apps\Account\Models\Schema\{UserSchema};";
           $content .= "\nuse {$namespace}\Apps\Account\Services\{$namespace}AuthService;";
           $content .= "\nuse SaQle\Config\Config;";
           $content .= "\nuse {$namespace}\Session\{$namespace}SessionHandler;";
           $content .= "\nuse {$namespace}\Apps\Account\Notifications\{WelcomeEmailSetup};";
           $content .= "\nuse {$namespace}\Apps\Account\Data\AccountsDbContext;";
           $content .= "\nuse SaQle\Dao\DbContext\DbTypes;";
           $content .= "\nuse SaQle\Dao\DbContext\DbPorts;";
           $content .= "\nuse {$namespace}\Config\Seeds\{$namespace}DbSeed;";
           $content .= "\n";
           $content .= "\n$"."session_domain = 'www.{$domain}.local';";
           $content .= "\n";
           $content .= "\nreturn [";
           $content .= "\n\t'db_context_classes'             => [";
           $content .= "\n\t\tAccountsDbContext::class => [";
           $content .= "\n\t\t\t'name'     => '{$domain}', ";
           $content .= "\n\t\t\t'type'     => DbTypes::MYSQL, ";
           $content .= "\n\t\t\t'port'     => DbPorts::MYSQL, ";
           $content .= "\n\t\t\t'username' => '', ";
           $content .= "\n\t\t\t'password' => ''";
           $content .= "\n\t\t],";
           $content .= "\n\t],";
           $content .= "\n\t'display_errors'                 => 1,";
           $content .= "\n\t'display_startup_errors'         => 1,";
           $content .= "\n\t'root_domain'                    => 'https://'.$"."session_domain.'/',";
           $content .= "\n\t'installed_apps'                 => ['account'],";
           $content .= "\n\t'auth_model_class'               => UserSchema::class,";
           $content .= "\n\t'auth_backend_class'             => BooiboAuthService::class,";
           $content .= "\n\t'media_folder'                   => 'mediacontentbooibo',";
           $content .= "\n\t'hidden_media_folder'            => true,";
           $content .= "\n\t'database_user'                  => 'saqlecom_dbmanager',";
           $content .= "\n\t'database_password'              => 'hxgdM$-j5QWZ',";
           $content .= "\n\t'database_name'                  => 'booibo',";
           $content .= "\n\t'session_domain'                 => $"."session_domain,";
           $content .= "\n\t'session_handler'                => BooiboSessionHandler::class,";
           $content .= "\n\t'email_username'                 => 'support@booibo.com',";
           $content .= "\n\t'email_password'                 => 'SiuEZTW@m+!p',";
           $content .= "\n\t'email_host'                     => 'mail.booibo.com',";
           $content .= "\n\t'email_port'                     => 465,";
           $content .= "\n\t'email_sender_name'              => 'Booibo Team',";
           $content .= "\n\t'email_sender_address'         => 'support@booibo.com',";
           $content .= "\n\t'welcome_email_setup_class'      => WelcomeEmailSetup::class,";
           $content .= "\n\t'verification_email_setup_class' => VerificationCodeEmailSetup::class,";
           $content .= "\n\t'role_model_class'               => RoleSchema::class,";
           $content .= "\n\t'permission_model_class'         => PermissionSchema::class,";
           $content .= "\n\t'model_auto_cm_fields'           => true,";
           $content .= "\n\t'model_auto_cmdt_fields'         => true,";
           $content .= "\n\t'model_soft_delete'              => true,";
           $content .= "\n\t'enable_multitenancy'            => false,";
           $content .= "\n\t'page_controller_class'          => Page::class,";
           $content .= "\n\t'db_seeder'                      => BooiboDbSeed::class,";
           $content .= "\n\t'layout_image_path'              => '{{ root_domain }}static/images/layout',";
           $content .= "\n\t'auth_controller'                => Signin::class,";
           $content .= "\n\t'middleware'                     => [],";
           $content .= "\n\t'icons_image_path'               => '{{ root_domain }}static/images/icons',";
           $content .= "\n\t'rsc_base_url'                   => '{{ root_domain }}',";
           $content .= "\n\t'templates'                      => '{{ document_root }}/templates',";
           $content .= "\n\t'max_file_size'                  => 200";
           $content .= "\n]";
           $content .= "\n?>";

           return $content;
     }

     private function get_seeding_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Config\Seeds;";
           $content .= "\n";
           $content .= "\nuse SaQle\Migration\Seed\DbSeed;";
           $content .= "\n";
           $content .= "\nclass BooiboDbSeed extends DbSeed{";
           $content .= "\n\tpublic static function get_seeds() : array{";
           $content .= "\n\t\treturn [";
           $content .= "\n\t\t\t//['model' => Industry::class, 'file' => 'industries.php'],";
           $content .= "\n\t\t];";
           $content .= "\n\t}";
           $content .= "\n}";
           $content .= "?>";

           return $content;
     }

     private function get_webroutes_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\ndeclare(strict_types = 1);";
           $content .= "\n";
           $content .= "\nnamespace {$namespace}\Routes;";
           $content .= "\n";
           $content .= "\nuse SaQle\Routes\Route;";
           $content .= "\nuse {$namespace}\Controllers\Home";
           $content .= "\n";
           $content .= "\nreturn [";
           $content .= "\n\tnew Route(['POST', 'GET'], '/', Home::class),";
           $content .= "\n\tnew Route(['POST', 'GET'], '/home/', Home::class),";
           $content .= "\n];";
           $content .= "?>";

           return $content;
     }

     private function get_apiroutes_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\ndeclare(strict_types = 1);";
           $content .= "\n";
           $content .= "\nnamespace {$namespace}\Routes;";
           $content .= "\n";
           $content .= "\nuse SaQle\Routes\Route;";
           $content .= "\n";
           $content .= "\nreturn [";
           $content .= "\n];";
           $content .= "?>";

           return $content;
     }

     private function get_sessionhandler_file_contents($name){
           $namespace = ucfirst($name);

           $content = "<?php";
           $content .= "\nnamespace {$namespace}\Session;";
           $content .= "\n";
           $content .= "\nuse SaQle\Session\SessionHandler;";
           $content .= "\n";
           $content .= "\nclass BooiboSessionHandler extends SessionHandler{";
           $content .= "\n";
           $content .= "\n}";
           $content .= "\n?>";

           return $content;
     }


}
