<?php

namespace SaQle\Lib;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder
};

class Lib extends Module {

	 public function contribute(ModuleBuilder $module): void {

         $module->routes()->prefix("")->name("");

     }

}