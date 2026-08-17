<?php

namespace SaQle\Lib;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder,
	 AdminModule
};

class Lib extends Module {

	 public function contribute(ModuleBuilder $module): void {

         $module->routes()->prefix("")->name("");

     }

}