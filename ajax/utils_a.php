<?php
require_once $_SERVER['DOCUMENT_ROOT']."/utilities/Vo/ajaxrequesthandler.php";
class UtilsAjax extends AjaxRequestHandler
{
	 public function __construct()
	 {
		 parent::__construct();
		 $this->init();
	 }
	 protected function init()
	 {
		 
	 }
	 public function listen()
	 {
		 if(isset($_POST['sendChatMessage']))
		 {
			 $decorator = new \Chats\CommitChatDecorator(new \Chats\ActiveChat());
			 $added_chat = $decorator->commitChat(array(
				 "message_from"=>$_POST['messageFrom'],
				 "message_to"=>$_POST['messageTo'],
				 "message"=>$_POST['message'],
				 "class_id"=> trim($_POST['classId']) !== "" ? trim($_POST['classId']) : NULL,
				 "chat_files"=>$_FILES['brylliansChatWindowFileAttachment']
			 ));
			 print json_encode($added_chat);
		 }
	 }
}
$utilsajax = new UtilsAjax();
$utilsajax->listen();
?>