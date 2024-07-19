<?php
//start session and make it read only: this will prevent session locks:
session_start();
//session_write_close();

//disable default disconnect:
ignore_user_abort(true);

//set headers for the stream:
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

//include required files:
require_once "callClass.php";
require_once "callUtilities.php";
require_once "userUtilities.php";


echo ":".str_repeat(" ", 2048)."\n"; //2kb padding for IE
echo "retry: 2000\n";

if( connection_aborted())
{
	 exit;
}
else
{
     $callUtility = new CallUtilities();
	 $userUtility = new UserUtilities("");

	 $latestCall = $callUtility->getLatestIncomingCall($_SESSION['userid']); //check if there is a new call for this user:
	 if( isset($latestCall->call_id) && !in_array($latestCall->call_id, $_SESSION['incoming_calls']) && $latestCall->status_code == 0)
	 {
		 $_SESSION['current_call'] = $latestCall;
		 array_push($_SESSION['incoming_calls'], $latestCall->call_id);
		 $latestCall->caller_info = $userUtility->fetchInformationForCollegeStudent($latestCall->call_from);
		 $data = json_encode($latestCall);
		 echo "id: " .$latestCall->call_id."\n";
		 echo "data: ".$data."\n\n";
		 ob_flush();
		 flush();
	 }
	 elseif(isset($_SESSION['current_call']) ) //the user is currently on a call:
	 {
		 $current_call = $callUtility->fetchCallDetails($_SESSION['current_call']->call_id);
		 $call_to_return = $current_call;
		 if($current_call->ice_text != $_SESSION['current_call']->ice_text)
		 {
			 if(is_null($current_call->ice_text) || $current_call->ice_text == "")
			 {
				 $call_to_return->ice_text = "False Positive";
			 }
			 $call_to_return->new_candidate = 1;
		 }
		 
		 $_SESSION['current_call'] = $current_call;//update sessions current call:
		 $data = json_encode($current_call);
		 $lastCallId = $current_call->call_id;
		 echo "id: " .$lastCallId."\n";
		 echo "data: ".$data."\n\n";
		 ob_flush();
		 flush();
	 }
	 else
	 {
		 //no need to send data
		 echo ": heartbeat\n\n";
		 ob_flush();
		 flush();
	 }
}
//5 second sleep then carry on
sleep(0);
?>
