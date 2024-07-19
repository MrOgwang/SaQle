<?php
namespace SaQle\Controllers\Data;

class PanelBody{
	private array  $data_sources;
	private bool   $add_checkboxes = false;
	private array  $data;
	private array  $table_properties = [];
	private bool   $include_authors  = false;
	private string $authors_header  = "";
	public function set_data_sources(array $data_sources){
		$this->data_sources = $data_sources;
	}
	public function set_add_checkboxes(bool $add_checkboxes){
		$this->add_checkboxes = $add_checkboxes;
	}
	public function set_include_authors(bool $include, string $header){
		$this->include_authors = $include;
		$this->authors_header  = $header;
	}
	public function get_add_checkboxes(){
		return $this->data && $this->add_checkboxes;
	}
	public function set_data(array $data){
		$this->data = $data;
	}
	public function set_table_properties(){
		$this->table_properties = $properties;
	}
	public function get_table_properties(){
		return $this->table_properties;
	}
	public function get_data(){
		return $this->data;
	}
	public function is_data_available(){
		 return $this->data && count($this->data) > 0 ? true : false;
	}

	private function construct_order_by($author){
		  $full_name = $author ? $author->first_name ." ".$author->last_name : "Unknown";
		  $user_name = $author ? $author->username : "";
      return "
      <div class='orderbycell flex'>
          <img src='{{ layout_image_path }}/usericon.png'>
          <div class='flex v_center orderbycellinfo'>
              <div>
                   <h3>{$full_name}</h3>
                   <p>{$user_name}</p>
              </div>
          </div>
      </div>
      ";
     }

    private function construct_checkbox($item){
          return "
          <div>
               <input class='form_input_check' type='checkbox'>
               <label></label>
          </div>
          ";
     }

     private function get_ellipsis_dd($item, $item_actions){
     	 $menu = "";
     	 foreach($item_actions as $a){
     	 	$properties = $a->get_properties();
     	 	$data_item = '';
     	 	if(array_key_exists("data", $properties)){
     	 		$data_item = $properties['data'];
     	 		$data_item = gettype($data_item) == "object" ? $data_item($item) : $data_item;
     	 		$data_item = json_encode($data_item);
     	 	}
     	 	$alink   = $a->get_link();
     	 	$linkval = gettype($alink) == "object" ? $alink($item) : $alink;
     	 	$menu .= "
     	 	<a data-item='{$data_item}' data-action='{$a->get_label()}' style='padding: 10px; padding-left: 20px; padding-right: 20px;' class='ordersactionlink flex' href='{$linkval}'>{$a->get_label()}</a>
     	 	";
     	 }
          return "
          <div style='position: relative;'>
                <span class='flex center orderactionsellipsis'><i data-lucide='more-vertical'></i></span>
                <div style='border-radius: 5px; padding-top: 10px; padding-bottom: 10px; background-color: #fff; box-shadow: 0px 0px 5px #a0a0a0; position: absolute; min-width: 100px; height: auto; left: 40px; top: -10px; z-index: 1000;' class='line_item_actions_dd hide'>
                    {$menu}
                </div>
          </div>
          ";
     }

	private function get_table(){
		$headercolsview = $this->get_header_colsview();
		$bodyrowsview   = $this->get_body_rows();
		return "
		<table>
			 <thead>
				 <tr>
					 {$headercolsview}
				 </tr>
			 </thead>
			 <tbody>
			     {$bodyrowsview}
			 </tbody>
		 </table>
		";
	}
	private function get_header_colsview(){
		$colsview = "";
		foreach($this->data_sources as $colname => $colval){
			$colsview .= "<th>{$colname}</th>";
		}
		return $colsview;
	}
	private function get_body_rows(){
		$rows = "";
		foreach($this->data as $d){
			$rows .= "<tr>";
			foreach($this->data_sources as $colname => $colval){
				$actualval = $colval($d);
				$rows .= "<td>{$actualval}</td>";
			}
			$rows .= "</tr>";
		}
		return $rows;
	}
	public function construct_panel_body(array $item_actions = []){
		if($this->include_authors){
			$author_info[$this->authors_header] = function($i){return $this->construct_order_by($i->author);};
			$this->data_sources = $author_info + $this->data_sources;
		}
		if(count($item_actions) > 0){
			$this->data_sources = ['&nbsp;' => function($i) use ($item_actions) {return $this->get_ellipsis_dd($i, $item_actions);}] + $this->data_sources;
		}
		if($this->add_checkboxes){
			$this->data_sources = ['' => function($i){return $this->construct_checkbox($i);}] + $this->data_sources;
		}

		$table = $this->get_table();
		return "
		<div class='fuel-app-data-panel-body'>
		     <!--------------->
		     {$table}
		     <!--------------->
	    </div>
		";
	}
}
?>