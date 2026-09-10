<?php

namespace SaQle\Orm\Connection;

use SaQle\Auth\Context\ActorContext;

class ConnectionTarget {

	 static public function make(string $logical_target) : string {

	 	 $parts = explode(".", $logical_target);

	 	 $conn_name = $parts[0];
		 $logical_db_name = $parts[1];
		 
		 $conn_config = config('db.connections')[$conn_name];

		 $db_config = $conn_config['databases'][$logical_db_name];

		 $physical_db_name = $db_config['name'];

		 if(config('tenancy.enabled') && $logical_db_name !== "system"){

		 	 $tenant = !ActorContext::is_system() ? request()->tenant : command()->attributes->get('tenant', null);

		 	 if($tenant){
		 	 	 $physical_db_name = $physical_db_name.'_'.strtolower(str_replace(" ", "_", $tenant->tenant_name));
		 	 }
		 } 

		 return $physical_db_name;
	 }

}