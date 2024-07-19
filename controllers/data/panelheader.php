<?php
namespace SaQle\Controllers\Data;

class PanelHeader{
	private $properties;
	public function __construct(
		private ?MenuItemCollection $menuitems = null,
		private ?Search $search                = null,
		...$properties
	){
		$this->properties = $properties;
	}

	public function get_properties(){
		return $this->properties;
	}

    public function get_item_actions(){
    	return $this->menuitems->get_item_actions();
    }

	public function construct_panel_header(bool $add_checkboxes, bool $is_data = true){
		$panelheader = "";
		if($add_checkboxes){
			$this->menuitems->unshift((new MenuItem(label: 'Mark', icon: 'check', id: 'checkItems')));
		}
		if($this->menuitems || $this->search){
			$menuitemview = "";
			$searchview   = "";
			if($this->menuitems && $this->menuitems->has_items()){
				$items         = $this->menuitems->get_menu_items();
				$items_count   = $this->menuitems->has_items();
				$display_count = $this->menuitems->get_display_count();
				for($i = 0; $i < $items_count; $i++){
					if($i <= $display_count - 1){
						$location = $items[$i]->get_location();
						if($location == "BOTH" || $location == "MENU_BAR"){
							$menuitemview .= $items[$i]->construct_menu_item();
						}
					}
				}
				if($items_count > $display_count){
					$menuitemview .= "
					span class='flex v_center fuel-menu-more-lnk' href='#'>
						 <span class='flex v_center'><i data-lucide='more-vertical'></i></span>
						 <span class='flex v_center'>More</span>
						 <div class='hide fuel-menu-dd'>
					";
					for($i = $display_count; $i < $items_count; $i++){
						$menuitemview .= $items[$i]->construct_menu_item();
					}
					$menuitemview .= "
					     </div>
					 </span>
					";
				}
			}
			if($this->search && $is_data){
				$searchview = $this->search->construct_search();
			}

			$panelheader = "
			<div class='flex v_center fuel-app-data-panel-header'>
				 <div class='flex v_center fuel-app-data-panel-header-left'>
					 {$menuitemview}
				 </div>
				 <div class='flex v_center row_reverse fuel-app-data-panel-header-right'>
					 {$searchview}
				 </div>
			 </div>
			";
		}
		return $panelheader;
	}
}
?>