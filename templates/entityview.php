<?php
declare(strict_types = 0);
namespace SaQle\Templates;

function entityview(){
	return "
	<div id='saqlepdfviewer' class='hide flex center saqlepdfviewer-wrapper'>
	    <div class='saqlepdfviewer'>
	        <div class='flex v_center saqlepdfviewerheader'>
	            <div class='flex v_center saqlepdfviewerheadertitle'>
	                <h2 id='saqlepdfviewertitle'>Document Title</h2>
	            </div>
	            <div class='flex v_center row_reverse saqlepdfviewerheaderclose'>
	                <span id='saqlepdfviewerclose' title='Close' class='flex v_center'><i data-lucide='x'></i></span>
	            </div>
	        </div>
	        <div id='saqlepdfviewerbody' class='saqlepdfviewerbody'>
	             <object data='{{ layout_image_path }}/samplepdf.pdf' type='application/pdf' aria-labelledby='PDF document'>
                     <p>Your browser does not support PDFs. <a href='{{ layout_image_path }}/samplepdf.pdf'>Download the PDF</a></p>
                 </object>
	        </div>
	    </div>
	</div>
	<div class='flex v_center app-form-header postbnd-admin-content-header'>
	     <div class='flex v_center'>
	         <a style='text-decoration:none;' class='backlink flex v_center' href='{{ back_url }}'>
	             <i data-lucide='move-left'></i>&nbsp;Back
	         </a>
	     </div>
		 <div class='flex v_center'>
	         <h2>{{ title }}</h2>
	     </div>
	     <div class='flex v_center row_reverse'>
	         {{ save_button }}
	         {{ delete_button }}
	     </div>
	 </div>
	 <div>
		 <form method='POST' id='{{ delete_form_id }}'>
		     <input type='hidden' name='{{ delete_property }}' value='true'>
		 </form>
		 <form method='POST' enctype='multipart/form-data' id='{{ view_form_id }}'>
		     <input type='hidden' name='{{ edit_property }}' value='true'>
	     </form>
	     <div class='app-form-body postbnd-admin-content-body'>
		     <div class='saqle-view'>
		         {{ message }}
		         {{ view_controls }}
		     </div>
	     </div>
     </div>
	";
}
?>