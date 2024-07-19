<?php
namespace SaQle\Controllers\Data;

class MenuItemCollection{
	private array $menu_items = [];
	private int   $max        = 0;
	public function add(MenuItem $item){
		$this->menu_items[] = $item;
		$this->max          = count($this->menu_items);
	}
	public function unshift(MenuItem $item){
		array_unshift($this->menu_items, $item);
		$this->max = count($this->menu_items);
	}
	public function get_menu_items(){
		return $this->menu_items;
	}
	public function set_display_count(int $max){
		$this->max = $max;
	}
	public function get_display_count(){
		return $this->max;
	}
	public function has_items(){
		return count($this->menu_items);
	}
	public function get_item_actions(){
		$actions = [];
		foreach($this->menu_items as $m){
			$loc = $m->get_location();
			if(in_array($loc, ['LINE_ITEM', 'BOTH'])){
				$actions[] = $m;
			}
		}
		return $actions;
    }
}
?>