<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Interfaces\ISystemModel;
use SaQle\Orm\Entities\Model\Schema\Table;

class CliUser extends PlatformUser {
	 public function __construct(){
	 	 parent::__construct(...[
	 	 	 'first_name'     => 'Cli',
	 	 	 'last_name'      => 'System',
	 	 	 'username'       => 'saqle-cli',
	 	 	 'password'       => '$argon2id$v=19$m=65536,t=4,p=1$Q1JOM29MSEFRdHQzMm5KMg$+UVd357YI1B+9H/dezus0Un6e0Q1MIQapXzawQA45MI',
	 	 	 'is_super_admin' => 1,
	 	 	 'is_super_user'  => 1
	 	 ]);
	 }
}
