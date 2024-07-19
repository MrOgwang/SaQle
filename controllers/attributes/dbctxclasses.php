<?php
namespace SaQle\Controllers\Attributes;

use Attribute;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;

#[Attribute(Attribute::TARGET_CLASS)]
class DbCtxClasses{

    /**
     * An array of database context classes to be used by a controller
     * */
    private array $classes;

    public function __construct(array $classes){
    	 $this->classes = $classes;
    }

    public function get_classes(){
    	return $this->classes;
    }
}
?>