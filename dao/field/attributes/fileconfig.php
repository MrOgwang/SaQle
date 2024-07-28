<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Attributes;

use Closure;

#[\Attribute(Attribute::TARGET_PROPERTY)]
class FileConfig{
	 /**
	 * Create a new file field instance
	 * @param Closure | string $path
	 * @param nullable Closure $rename_callback
	 * @param nullable array $crop_dimensions
	 * @param nullable array $resize_dimensions
	 */
	 public function __construct(
	 	 private Closure|string  $path, 
	 	 private ?Closure        $rename_callback   = null,
	 	 private ?Closure        $default           = null,
	 	 private ?array          $crop_dimensions   = null,
	 	 private ?array          $resize_dimensions = null,
	 	 private ?Closure        $show_file         = null
	 ){}
	 /*setters*/
	 public function set_path(Closure|string $path){
		 $this->path = $path;
	 }
	 public function set_rename_callback(Closure $callback){
	     $this->rename_callback = $callback;
	 }
	 public function set_default(Closure $callback){
	     $this->default = $callback;
	 }
	 public function set_crop_dimensions(array $dimensions){
	     $this->crop_dimensions = $dimensions;
	 }
	 public function set_resize_dimensions(array $dimensions){
	     $this->resize_dimensions = $dimensions;
	 }
	 public function set_show_file(string $show_file){
	 	$this->show_file = $show_file;
	 }
	 
	 /*getters*/
	 public function get_path() : Closure|string{
		 return $this->path;
	 }
	 public function get_rename_callback() : Closure{
	     return $this->rename_callback;
	 }
	 public function get_default() : Closure{
	     return $this->default;
	 }
	 public function get_crop_dimensions() : array{
	     return $this->crop_dimensions;
	 }
	 public function get_resize_dimensions() : array{
	     return $this->resize_dimensions;
	 }
	 public function get_show_file(){
	 	return $this->show_file;
	 }
}
?>
