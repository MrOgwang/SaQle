<?php
use SaQle\Templates\Attributes\{Template, ParentTemplate, Css, Js, Controller};
use Morewifi\Apps\Backoffice\Controllers\AddCategory;

#[Template()]
#[ParentTemplate(path: "apps/backoffice/templates/backoffice", name: "backoffice", context_key: "backoffice_content")]
#[Controller(controller: AddCategory::class)]
#[Css(files: ['backoffice/addform', 'backoffice/multiselect'])]
#[Js(files: ['backoffice/addform', 'backoffice/multiselect'])]
function addcategory(){
	return "
	<form method='POST' enctype='multipart/form-data' class='addform'>
	    <div class='flex v_center addformheader'>
	        <div>
		        <div class='formtitle flex v_center'>
		           <h3>New Category</h3>
		        </div>
		        <div class='backlink flex v_center'>
		            <a id='addformbacklink' class='flex v_center' href='#'>
		              <span><i data-lucide='chevron-left'></i></span>
		              <span>Back</span>
		           </a>
		        </div>
		        <div class='flex v_center row_reverse'>
		           <button type='submit' class='form_button_action'>Save</button>
		        </div>
		    </div>
	    </div>
	    <div class='addformmessage'>
	        {{ message }}
	    </div>
	    <div class='addformbody'>
	       {{ controls }}
	    </div>
	</form>
	";
}
?>