<?php
namespace SaQle\Commons;
trait DateUtils{
	 public static function current_date(){
		 date_default_timezone_set(DEFAULT_TIMEZONE);
         $current_date_time = new \DateTime();
         $current_date = date(SYSTEM_DATE_FORMAT, $current_date_time->getTimestamp());
         return $current_date;
	 }
	 public static function current_time(){
         date_default_timezone_set(DEFAULT_TIMEZONE);
         $current_date_time =  new \DateTime();
         $current_time = date('h:i:s a', $current_date_time->getTimestamp());
         return $current_time;
	 }
	 private static function get_separator(){
	 	return match(SYSTEM_DATE_FORMAT){
	 		'Y-m-d', 'Y-d-m', 'd-Y-m', 'd-m-Y', 'm-d-Y', 'm-Y-d' => '-',
	 		'Y/m/d', 'Y/d/m', 'd/Y/m', 'd/m/Y', 'm/d/Y', 'm/Y/d' => '/'
	 	};
	 }
	 private static function get_d_value($name, $current_date = null){
	 	$current_date      = $current_date ?? self::current_date();
	 	$date_parts        = explode(self::get_separator(), $current_date);
	 	$date_format_parts = explode(self::get_separator(), SYSTEM_DATE_FORMAT);
	 	$index             = array_search($name, $date_format_parts);
	 	return $date_parts[$index];
	 }
	 public static function current_day($date = null){
	 	return self::get_d_value(name: 'd', current_date: $date);
	 }
	 public static function current_month($date = null){
		 return self::get_d_value(name: 'm', current_date: $date);
	 }
	 public static function current_year($date = null){
	 	 return self::get_d_value(name: 'Y', current_date: $date);
	 }
	 public static function current_hour($time = null){
		 $current_time = $time ?? self::current_time();
		 return explode(":", $current_time)[0];
	 }
	 public static function current_mins($time = null){
		 $current_time = $time ?? self::current_time();
		 return explode(":", $current_time)[1];
	 }
	 public static function current_secends($time = null){
		 $current_time = $time ?? self::current_time();
		 return explode(":", $current_time)[2];
	 }
	 public static function convert_date_2_format(string $date, string $format, string $nformat = ""){
	 	 $nformat = $nformat ? $nformat : SYSTEM_DATE_FORMAT;
         $res = date_create_from_format($format, $date);
         return date_format($res, $nformat);
     }
	 public static function format_date($date = null, $format = null){
	     date_default_timezone_set(DEFAULT_TIMEZONE);
		 $format = $format ?? DATE_ADDED_FORMAT;
		 if(is_null($date))
			 return date($format, time());
		 $timestamp = is_string($date) ? mktime(0, 0 , 0, self::current_month($date), self::current_day($date), self::current_year($date)) : $date;
		 return date($format, $timestamp);
	 }
     public static function time_diff($time_one, $time_two){
         date_default_timezone_set(DEFAULT_TIMEZONE);
		 $first = new \DateTime("@".$time_one);
		 $first->setTimezone(new \DateTimeZone(DEFAULT_TIMEZONE));
		 $second = new \DateTime("@".$time_two);
		 $second->setTimezone(new \DateTimeZone(DEFAULT_TIMEZONE));
		 
		 $diff = $second->diff($first);
		 $weeks = floor($diff->format('%a') / 7);
		 $days = floor($diff->format('%a') % 7);
		 $hours = floor($diff->format('%h'));
		 $minutes = floor($diff->format('%i'));
		 $seconds = floor($diff->format('%s'));

		 $week = new \stdClass();
		 $week->label = "week";
		 if($weeks > 1) $week->label = "weeks";
		 $week->count = $weeks;

		 $day = new \stdClass();
		 $day->label = "day";
		 if($days > 1) $day->label = "days";
		 $day->count = $days;

		 $hour = new \stdClass();
		 $hour->label = "hour";
		 if($hours > 1) $hour->label = "hours";
		 $hour->count = $hours;

		 $min = new \stdClass();
		 $min->label = "minute";
		 if($minutes > 1) $min->label = "minutes";
		 $min->count = $minutes;

		 $sec = new \stdClass();
		 $sec->label = "second";
		 if($seconds > 1) $sec->label = "seconds";
		 $sec->count = $seconds;

		 $time_parts = array($week, $day, $hour, $min, $sec);
		 $clean_parts = array();
		 foreach($time_parts as $part){
			 if($part->count > 0) array_push($clean_parts, $part);
		 }
		 return $clean_parts;
     }
	 static public function get_yesterday($ctime){
		 $current_date = self::current_date($ctime);
         $current_date_array = explode("-", $current_date);
         $current_year = $current_date_array[0];
         $current_month = $current_date_array[1];
         $current_day = $current_date_array[2];	
         $yesterday_day = $current_day - 1;
         if($yesterday_day != 0){
			$yesterday_date = $current_year ."-" .$current_month ."-" .$yesterday_day;
		 }else{
		     //if the difference between today and yesterday is zero, then yesterday must have been the prevoius month:
		     $yesterday_month = $current_month - 1;
		     if($yesterday_month != 0){
			     //get the number of days for that previous month:
		         $ts = mktime(0, 0, 0, $yesterday_month, 1, $current_year);
	             $number_of_days = date("t", $ts);
		         //assign yesterdayDay to the last day of this month:
		         $yesterday_day = $number_of_days;  
			     $yesterday_date = $current_year ."-" .$yesterday_month ."-" .$yesterday_day;
		     }else{
			     //if yesterday_month is zero, then yesterday must have been the prevoius year:
			     $yesterday_year = $current_year - 1;
			     $yesterday_month = 12;
			     $yesterday_day = 31;
			     $yesterday_date = $yesterday_year ."-" .$yesterday_month ."-" .$yesterday_day;
		     }
		 }	
         return $yesterdayDate;		
	 }
	 static public function time_diff_string($time_parts){
		 $diff_string = "";
		 if(count($time_parts) > 0){
			 for($i = 0; $i < count($time_parts); $i++){
				 $diff_string .= $time_parts[$i]->count ." ".$time_parts[$i]->label;
				 if($i == count($time_parts) - 2) $diff_string .= " and ";
				 if($i != count($time_parts) - 2 && $i != count($time_parts) - 1) $diff_string .= ", ";
			 }
		 }else{
			$diff_string = "Just now.";
		 }
		 return  $diff_string;
	 }
	 static public function time_to_24($time){ //time here is in the format hh:mm:ss am/pm
		 return date("H:i:s", strtotime($time));
	 }
	 static public function to_timestamp($string_date = "", $string_time = ""){
		 if(!$string_date && !$string_time) return time();
		 if($string_date && !$string_time) $string_time = self::current_time();
		 if(!$string_date && $string_time) $string_date = self::current_date();
		 $string_time = self::time_to_24($string_time);
		 return mktime(
			 (int)self::current_hour($string_time),
			 (int)self::current_mins($string_time),
			 (int)self::current_secends($string_time),
			 (int)self::current_month($string_date),
			 (int)self::current_day($string_date),
			 (int)self::current_year($string_date)
		 );
	 }
	 /*
	     - this function constructs a date picker calendar widget.
		 @param string $current_date: the current date, either as set by the user or server current date.
		     the current date must in the format dd-mm-yyyy.
		 
	 */
	 static public function calendar_widget($current_date = null){
		 $current_date = (!is_null($current_date)) ? $current_date : self::current_date();
		 $current_date_parts = explode("-", $current_date);
		 $year = $current_date_parts[2];
		 $month = $current_date_parts[1];
		 $day = $current_date_parts[0];
	     $timestamp = mktime(0, 0, 0, $month, $day, $year);
	     $number_of_days = date("t", $timestamp);
	     $month_name = date("F", $timestamp);
	     $year_name = date("o", $timestamp);
	     $days_array = ["", "", "", "", "", "", "",
	                         "", "", "", "", "", "", "",
				             "", "", "", "", "", "", "",
				             "", "", "", "", "", "", "",
				             "", "", "", "", "", "", ""];
	  
	     $first_day_of_month_code = 0;
	     for($day_index = 1; $day_index <= $number_of_days; $day_index++){
		     $day_timestamp = mktime(0, 0, 0, $month, $day_index, $year);
		     if($day_index === 1){
			     $first_day_of_month_code = date("w", $day_timestamp);
		         $diff = 6 - $first_day_of_month_code; 
		     }
		     $current_week = 0;
		     $accumilated_days = 0;
		     $day_of_week_code = 0;
		     if(($day_index >= 1) && ($day_index <= $diff + 1)){
			     $v= $diff + 1;
		         $current_week = 0;
			     $accumilated_days = 0;
			     $day_of_week_code = date("w", $day_timestamp);
		     }
		     if(($day_index > $diff + 1) && ($day_index <= $diff + 1 + 7)){
		         $current_week = 1;
			     $accumilated_days = 6;
			     $day_of_week_code = date("w", $day_timestamp);
		     }
		     if(($day_index > $diff + 1 + 7) && ($day_index <= $diff + 1 + 7 + 7)){
		         $current_week = 2;
			     $accumilated_days = 12;
			     $day_of_week_code = date("w", $day_timestamp);
		     }
		     if(($day_index > $diff + 1 + 7 + 7) && ($day_index <= $diff + 1 + 7 + 7 + 7)){
		         $current_week = 3;
			     $accumilated_days = 18;
			     $day_of_week_code = date("w", $day_timestamp);
		     }
		     if($day_index > $diff + 1 + 7 + 7 + 7){
		         $current_week = 4;
			     $accumilated_days = 24;
			     $day_of_week_code = date("w", $day_timestamp);
		     }
		     //get appropriate index for day of the month.
		     $month_day_index = $current_week + $accumilated_days + $day_of_week_code;
		     if($days_array[$month_day_index] == ""){
			     $days_array[$month_day_index] = $day_index; 
		     }else{
			     $days_array[$day_of_week_code] = $day_index;
		     }
	     }
		 $previous_month = ((int)$month > 1) ? (int)$month - 1 : 1;
		 $next_month = ((int)$month < 12) ? (int)$month + 1 : 12;
		 $years_to_cover = self::get_years();
		 $date_picker = "
	     <div class='calendar'>
			 <div class='calendarmonths'>
			     <div class='monthsnav'>
				     <a href='#' data-premonth='$previous_month' class='premonth'><span class='fa fa-chevron-left'></span></a>
				     <div class='currentmonth'>
					     $month_name $year_name
				     </div>
					 <a href='#' data-nextmonth='$next_month' class='nextmonth'><span class='fa fa-chevron-right'></span></a>
				 </div>
				 <div class='years'>
				     <select class='calendar_widget_years'>
					     $years_to_cover
					 </select>
				 </div>
			 </div>
			 <div class='calendarweeks'>
				 <div>Su</div>
				 <div>Mo</div>
				 <div>Tu</div>
				 <div>We</div>
				 <div>Th</div>
				 <div>Fr</div>
				 <div>Sa</div>
			 </div>
			 <div class='celendardays'>
	             <div class='calendardayrow'>
		 ";
	     $d = (int)self::current_day($current_date);
		 $week_days_count = 0;
	     for($y = 0; $y < count($days_array); $y++){
			 $today_highlight = ($days_array[$y] === $d) ? "calendardaycoltoday" : "";
			 $calendarday_column_value = ($days_array[$y] != "") ? $days_array[$y] : "";
			 $calendar_day_class = ($days_array[$y] != "") ? "calendardaycol" : "calendardaycolempty";
		     $current_date = ($days_array[$y] != "") ? self::pre_pend_leading_zeros($days_array[$y], 2) ."-".$month."-".$year: "";
			 if(($week_days_count >= 0) && ($week_days_count <= 6)){
			     $date_picker .= "<div class='$calendar_day_class $today_highlight' data-date='$current_date'>$calendarday_column_value</div>";
				 $date_picker .= ($y == count($days_array) - 1) ? "</div>" : ""; //if this is the last day, close the previous row of days.
		     }else{
				 $week_days_count = 0; //re-initialise the week_days_count, because we are starting another week.
				 $date_picker .= "</div><div class='calendardayrow'><div class='$calendar_day_class $today_highlight' data-date='$current_date'>$calendarday_column_value</div>"; //close the previous row of days, start a new row and add this day.
				 $date_picker .= ($y == count($days_array) - 1) ? "</div>" : "";
		     }
		     $week_days_count += 1;
	     }
	     $date_picker .= "
		     </div>
		 </div>";
	     return $date_picker;
     }
	 /*the following functions gets the number of years to be included after or before the current year
	  depending on the current date set by the user or system.
	  - @param string $current_date: the current date in the format dd-mm-yyyy.
	  - @param integer $range: the number of years into the future or past from the current year. range defaults to 100 years if not provided.
	  - @param boolean $include_future: whether to include years in the future. not advisable for constructing a date picker.
	 */
     static public function get_years($current_date = null, $range = 100, $include_future = false){
		 $current_date = (!is_null($current_date)) ? $current_date : self::current_date();
		 $current_date_parts = explode("-", $current_date);
		 $current_year = (int)$current_date_parts[2];
		 $years = array($current_year);
		 //get ten years before this year:
		 for($y = 1; $y <= $range; $y++){
		     $last_year = $current_year - $y;
		     array_push($years, $last_year);
		 }
		 if($include_future == true){
			 for($y = 1; $y <= $range; $y++){
		         $next_year = $current_year + $y;
		         array_push($years, $next_year);
		     }
		 }
		 rsort($years, SORT_NUMERIC);
		 $years_options = "";
		 for($x = 0; $x < count($years); $x++){
			 $selected = ($years[$x] == $current_year) ? "selected" : "";
		     $years_options .= "<option value='$years[$x]' $selected>$years[$x]</option>";
	     }
		 return $years_options;
	 }
	 //this function gets the date for today depending on the current time set by the user:
	 static function get_today($ctime){
		 $today = "";
		 if($ctime != null){
			 $ctime_array = explode("-", $ctime);
		     $cyear = $ctime_array[0];
			 if(count($ctime_array) == 2){
				 $cmonth = $ctime_array[1];
				 $cday = 1;
			 }elseif(count($ctime_array) == 3){
				 $cmonth = $ctime_array[1];
				 $cday = $ctime_array[2];
			 }else{
				 $cmonth = 1;
				 $cday = 1;
			 }
			 $today = $cyear ."-" .$cmonth  ."-" .$cday;
		 }else{
	         $time_array = getdate(time());
	         $year = $time_array['year'];
	         $m = $time_array['mon'];
	         $d = $time_array['mday'];
		     $today = $year ."-" .$m  ."-" .$d;
		 }
		 return $today;
	 }
	 static public function current_date_details(){
	     date_default_timezone_set(DEFAULT_TIMEZONE);
		 $date_details = new \stdClass();
		 $current_date = new \DateTime(); //get the current date and time.
		 $date_details->day_of_month = date('d', $current_date->getTimestamp());
		 $date_details->day_of_year = date('z', $current_date->getTimestamp());
		 $date_details->day_of_week = date('w', $current_date->getTimestamp());
		 $date_details->ordinal_suffix = date('S', $current_date->getTimestamp());
		 $date_details->abbr_weekday_name = date('D', $current_date->getTimestamp());
		 $date_details->full_weekday_name = date('l', $current_date->getTimestamp());
		 $date_details->week_number = date('W', $current_date->getTimestamp());
		 return $date_details;
	 }
	 static public function express_datetime_relativetonow($string_date, $string_time){
	     date_default_timezone_set(DEFAULT_TIMEZONE);
		 $expressed_time = self::format_date($string_date) ." ".$string_time;
		 $current_datetimestamp = time();
		 $entered_datetimestamp = $this->to_timestamp($string_date, $string_time);
		 if(date("Y", $current_datetimestamp) == date("Y", $entered_datetimestamp)){
			 if(date("n", $current_datetimestamp) == date("n", $entered_datetimestamp)){
				 if(date("W", $current_datetimestamp) == date("W", $entered_datetimestamp)){
					 if(date("d", $current_datetimestamp) == date("d", $entered_datetimestamp)){
						 if(date("h", $current_datetimestamp) == date("h", $entered_datetimestamp)){
							 $expressed_time = "Now";
						 }else{
							 $current_hour = (int)date("n", $current_datetimestamp);
							 $entered_hour = (int)date("n", $entered_datetimestamp);
							 if( $current_hour > $entered_hour && $current_hour - $entered_hour == 1) $expressed_time = "An hour ago";
							 if( $entered_hour > $current_hour && $entered_hour - $current_hour == 1) $expressed_time = "An hour from now";
							 if( $entered_hour != $current_hour && abs($entered_hour - $current_hour) > 1) $expressed_time = "Today " .date("h:m:s a", $entered_datetimestamp);
						 }
					 }else{
						 $current_day = (int)date("n", $current_datetimestamp);
						 $entered_day = (int)date("n", $entered_datetimestamp);
						 if( $current_day > $entered_day && $current_day - $entered_day == 1) $expressed_time = "Yesterday ".date("h:m:s a", $entered_datetimestamp);
						 if( $entered_day > $current_day && $entered_day - $current_day == 1) $expressed_time = "Tomorrow ".date("h:m:s a", $entered_datetimestamp);
						 if( $entered_day != $current_day && abs($entered_day - $current_day) > 1) $expressed_time = "This week " .date("l h:m:s a", $entered_datetimestamp);
					 }
				 }else{
					 $current_week = (int)date("n", $current_datetimestamp);
					 $entered_week = (int)date("n", $entered_datetimestamp);
					 if( $current_week > $entered_week && $current_week - $entered_week == 1) $expressed_time = "Last week ".date("l h:m:s a", $entered_datetimestamp);
					 if( $entered_week > $current_week && $entered_week - $current_week == 1) $expressed_time = "Next week ".date("l h:m:s a", $entered_datetimestamp);
					 if( $entered_week != $current_week && abs($entered_week - $current_week) > 1) $expressed_time = date("l jS h:m:s a", $entered_datetimestamp);
				 }
			 }else{
				 $current_month = (int)date("n", $current_datetimestamp);
				 $entered_month = (int)date("n", $entered_datetimestamp);
				 if( $current_month > $entered_month && $current_month - $entered_month == 1) $expressed_time = "Last month ".date("jS h:m:s a", $entered_datetimestamp);
				 if( $entered_month > $current_month && $entered_month - $current_month == 1) $expressed_time = "Next month ".date("jS h:m:s a", $entered_datetimestamp);
				 if( $entered_month != $current_month && abs($entered_month - $current_month) > 1) $expressed_time = date("F jS h:m:s a", $entered_datetimestamp);
			 }
		 }else{
			 $current_year = (int)date("Y", $current_datetimestamp);
			 $entered_year = (int)date("Y", $entered_datetimestamp);
			 if( $current_year > $entered_year && $current_year - $entered_year == 1) $expressed_time = "Last year ".date("F jS h:m:s a", $entered_datetimestamp);
			 if( $entered_year > $current_year && $entered_year - $current_year == 1) $expressed_time = "Next year ".date("F jS h:m:s a", $entered_datetimestamp);
			 if( $entered_year != $current_year && abs($entered_year - $current_year) > 1) $expressed_time = $this->format_date($string_date) ." ".$string_time;
		 }
         return $expressed_time;
	 }
	 public function get_date_for_today($day_number, $year){
		 $jan_stops = 31;
		 $feb_stops = ( $this->is_leap($year) ) ? 60 : 59;
		 $mar_stops = ( $this->is_leap($year) ) ? 91 : 90;
		 $apr_stops = ( $this->is_leap($year) ) ? 121 : 120;
		 $may_stops = ( $this->is_leap($year) ) ? 152 : 151;
		 $jun_stops = ( $this->is_leap($year) ) ? 182 : 181;
		 $jul_stops = ( $this->is_leap($year) ) ? 213 : 212;
		 $aug_stops = ( $this->is_leap($year) ) ? 244 : 243;
		 $sep_stops = ( $this->is_leap($year) ) ? 274 : 273;
		 $oct_stops = ( $this->is_leap($year) ) ? 305 : 304;
		 $nov_stops = ( $this->is_leap($year) ) ? 335 : 334;
		 $dec_stops = ( $this->is_leap($year) ) ? 366 : 365;
		 $feb_days = ( $this->is_leap($year) ) ? 29 : 28;
		 $day_stops = array($jan_stops, $feb_stops, $mar_stops, $apr_stops, $may_stops, $jun_stops, $jul_stops, $aug_stops, $sep_stops, $oct_stops, $nov_stops, $dec_stops);
		 $month_days = [31, $feb_days, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
		 $ds_index = 0;
		 $month = 0;
		 for($ds_index = 0; $ds_index < count($day_stops); $ds_index++){
			 if($day_stops[$ds_index] > $day_number){
				 if($ds_index != 0){
					 if($day_number > $day_stops[$ds_index - 1]){
					     $month = $ds_index;
				     }
				     if($day_number == $day_stops[$ds_index - 1]){
					     $month = $ds_index - 1;
				     }
					 break;
				 }
			 }else if($day_stops[$ds_index] == $day_number){
				 $month = $ds_index;
				 break;
			 }
		 }
		 $day = $month_days[$month] - ($day_stops[$month] - $day_number);
		 $return_val = new \stdClass();
		 $return_val->year = $year;
		 $return_val->month = $month;
		 $return_val->day = $day;
		 return $return_val;
     }
	 public function is_leap($year){
		 return ( $year % 4 == 0 ) ? true : false;
	 }
     public function get_weeks_in_year($year){
		 $days_in_year = ($this->is_leap($year)) ? 366 : 365;
		 $weeks = array();
		 $week_counter = 0;
		 for($day_of_year = 1; $day_of_year <= $days_in_year; $day_of_year++){
			 $current_date_parts = $this->get_date_for_today($day_of_year, $year);
			 $current_datestamp = mktime(0, 0, 0, $current_date_parts->month + 1, $current_date_parts->day, $current_date_parts->year);
			 $clean_date = $current_date_parts->day ."-" .($current_date_parts->month + 1)."-".$current_date_parts->year;
			 $current_week_day = date("N", $current_datestamp);
			 if($day_of_year == 1 || $current_week_day == 1){
				 $week_counter += 1;
				 $w = new \stdClass();
				 $w->week_number = $week_counter;
				 $w->week_name = "Week ".$week_counter;
				 $w->week_start_date = $clean_date;
				 $w->week_end_date = "";
				 array_push($weeks, $w);
			 }elseif($current_week_day == 7){
				 $weeks[ count($weeks) - 1]->week_end_date = $clean_date;
			 }
		 }
	     return $weeks;
	 }
	 
	 
}
