<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field;
enum FormControlTypes : string {
	 case TEXT          = "text";
	 case PASSWORD      = "password";
	 case HIDDEN        = "hidden";
	 case EMAIL         = "email";
	 case SEARCH        = "search";
	 case TEL           = "tel";
	 case URL           = "url";
	 case NUMBER        = "number";
	 case CHECKBOX      = "checkbox";
	 case RADIO         = "radio";
	 case FILE          = "file";
	 case TEXTAREA      = "textarea";
	 case SELECT        = "select";
	 case SLIDER        = "slider";
	 case DATE          = "date";
	 case DATETIMELOCAL = "datetime-local";
	 case MONTH         = "month";
	 case TIME          = "time";
	 case WEEK          = "week";
	 case COLOR         = "color";
}
?>