<?php
require_once "utilityFunctions.php";
class CallUtilities
{
	 use UtilityFunctions;
	 protected $db = ""; //a database reference.uug

	 public function __construct()
	 {
		 $this->db = $this->connectToDatabase(); //this function is defined in utilityFunctions.php
	 }
	 
	 public function makeCall($call)
	 {
		 $call_date = $this->getCurrentDate(); //date of call:
		 $call_time = $this->getCurrentTime(); //time of call:
		 $this->insertCallIntoTable($call, $call_date, $call_time); //insert call into table:
		 $call_id = $this->getIdOfInsertedCall($call, $call_date, $call_time); //get id of inserted call:
		 return $this->fetchCallDetails($call_id); //return details of call:
	 }
	 
	 private function insertCallIntoTable($call, $call_date, $call_time)
	 {
		 $sql = "INSERT INTO call_logs (call_from, call_to, call_date, call_time, status_code, status_message) VALUES (?, ?, ?, ?, ?, ?)";
		 $data = array($call->getCallFrom(), $call->getCallTo(), $call_date, $call_time, $call->getCallStatusCode(), $call->getCallStatusMessage());
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 private function getIdOfInsertedCall($call, $call_date, $call_time)
	 {
		 $call_id = -1;
		 $sql = "SELECT call_id FROM call_logs WHERE call_from = ? AND call_to = ? AND call_date = ? AND call_time = ?";
		 $data = array($call->getCallFrom(), $call->getCallTo(), $call_date, $call_time);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $call_id = $statement->fetchObject()->call_id;
		 }
		 return $call_id;
	 }
	 
	 private function getInsertedCall($call, $call_date, $call_time)
	 {
		 $call_object = new stdClass();
		 $sql = "SELECT * FROM call_logs WHERE call_from = ? AND call_to = ? AND call_call_date = ? AND call_time = ?"; //INSERT INTO call_logs (call_from, call_to, call_date, call_time) VALUES (?, ?, ?, ?)";
		 $data = array($call->getCallFrom(), $call->getCallTo(), $call_date, $call_time);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $call_object = $statement->fetchObject();
		 }
		 return $call_object;
	 }

	 public function fetchCallDetails($call_id)
	 {
		 $call_object = new stdClass();
		 $sql = "SELECT * FROM call_logs WHERE call_logs.call_id = ?";
		 $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() == 1)
		 {
			 $call_object = $statement->fetchObject();
		 }
		 return $call_object;
	 }
	 
	 public function deleteCallLogs()
	 {
       $sql = "DELETE FROM call_logs";
			 $statement = $this->makeStatement($this->db, $sql);
			 return true;
	 }

	 //set a call as having been answered:
	 public function receiveCall($call_id)
	 {
		 $sql = "UPDATE call_logs SET status_code = 1, status_message = 'RECEIVED_CALL' WHERE call_id = ?";
		 $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return $this->fetchCallDetails($call_id);
	 }

	 //the following function gets the calls made to this person:
	 public function getIncomingCalls($user_id)
	 {
		 $allCalls = array();
		 $sql = "SELECT call_id, call_from, call_to, call_date, call_time
          		 FROM video_call
				 INNER JOIN user_account ON video_call.call_from = user_account.user_id
				 WHERE call_to = ? AND call_answered = 0 AND call_ended = 0";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($call = $statement->fetchObject())
		 {
			 array_push($allCalls, $call);
		 }
		 return $allCalls;
	 }

	 public function getLatestIncomingCall($user_id)
	 {
		 $latestCall = new stdClass();
		 $sql = "SELECT * FROM call_logs
				 INNER JOIN call_parameters ON call_logs.call_id = call_parameters.call_id
				 WHERE call_to = ? AND status_code = 0 
				 ORDER BY call_logs.call_id DESC LIMIT 1";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $latestCall = $statement->fetchObject();
		 }
		 return $latestCall;
	 }

	 //the following gets the latest call made to this user:
	 public function getLatestCallMadeByUser($user_id)
	 {
		 $latestCall = new stdClass();
		 $sql = "SELECT call_logs.call_id, call_from, call_to, call_date, call_time,
		                user_id, first_name, last_name, user_photo
          		 FROM call_logs
				 INNER JOIN user_account ON call_logs.call_to = user_account.user_id
				 WHERE call_from = ? AND call_answered = 0 AND call_ended = 0
				 ORDER BY call_logs.call_id DESC LIMIT 1";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() == 1)
		 {
			 $latestCall = $statement->fetchObject();
		 }
		 return $latestCall;
	 }

	 //the following functiongets the call parameters given the id of a call:
	 private function getCallParameters($call_id)
	 {
		 $parameters = new stdClass();
		 $sql = "SELECT * FROM call_parameters WHERE call_id = ?";
		 $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() == 1)
		 {
			 $parameters = $statement->fetchObject();
			 $parameters->found = 1;
		 }
		 else
		 {
			 $parameters->found = 0;
		 }
		 return $parameters;
	 }

	 //the following function gets the calls made by this person:
	 public function getOutgoingCalls()
	 {
		 $allCalls = array();
		 $sql = "SELECT * FROM video_call WHERE call_from = ? AND call_answered = 0 AND call_ended = 0";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($call = $statement->fetchObject())
		 {
			 array_push($allCalls, $call);
		 }
		 return $allCalls;
	 }

	 //update the ice candidate for a call:
	 public function updateIceCandidate($call_id, $ice, $sender)
	 {
		 $sql = "UPDATE call_parameters SET ice = 1, ice_text = ?, ice_from = ? WHERE call_id = ?";
		 $data = array($ice, $sender, $call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }

	 //update the session description protocol offer:
	 public function updateSDPOffer($callId, $offer)
	 {
		 $sql = "UPDATE call_parameters SET offer = 1, offer_text = ? WHERE call_id = ?";
		 $data = array($offer, $callId);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 //update the session description complete:
	 public function updateSDPComplete($callId)
	 {
		 $sql = "UPDATE call_parameters SET sdp_complete = 1 WHERE call_id = ?";
		 $data = array($callId);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }

	 //update the session description protocol answere:
	 public function updateSDPAnswere($callId, $ans)
	 {
		 $sql = "UPDATE call_parameters SET answere = 1, answere_text = ? WHERE call_id = ?";
		 $data = array($ans, $callId);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }


	 private function checkIceCandidateExists($callId, $who = "caller")
	 {
		 $iceExists = 0;
		 if($who == "caller")
		 {
			 $sql = "SELECT ice_one FROM call_parameters WHERE call_id = ?";
		 }
		 else
		 {
			 $sql = "SELECT ice_two FROM call_parameters WHERE call_id = ?";
		 }

		 $data = array($callId);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() == 1)
		 {
			 $call = $statement->fetchObject();
			 if($who == "caller")
		     {
			     if($call->ice_one != 0)
				 {
					 $iceExists = 1;
				 }
		     }
		     else
		     {
			     if($call->ice_two != 0)
				 {
					 $iceExists = 1;
				 }
		     }

		 }
		 return $iceExists;
	 }

	 public function setCallFailed($call_id)
	 {
		 $sql = "UPDATE call_logs SET status_code = 42, status_message = 'CALL_FAILED' WHERE call_id = ?";
	     $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }

	 public function setCallTimedout($call_id)
	 {
		 $sql = "UPDATE call_logs SET status_code = 32, status_message = 'CALL_TIMEOUT' WHERE call_id = ?";
		 $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }

	 public function setCallRejected($call_id)
	 {
		 $sql = "UPDATE call_logs SET status_code = 41, status_message = 'CALL_REJECTED' WHERE call_id = ?";
	     $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
	     return true;
	 }

	 public function setCallCanceled($call_id)
	 {
		 $sql = "UPDATE call_logs SET status_code = 31, status_message = 'CALL_CANCELED' WHERE call_id = ?";
	     $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 public function resetCallParameters($call_id)
	 {
		 $sql = "UPDATE call_parameters SET ice = 0, ice_text = '', offer = 0, offer_text = '',
		                 answere = 0, answere_text = '', ice_from = 0, sdp_complete = 0
             	WHERE call_id = ?";
		 $data = array($call_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 public function sendFilesToContacts($sent_from, $recipients, $files)
	 {
		 $sentFiles = array();
		 $date_sent = $this->getCurrentDate();
		 $time_sent = $this->getCurrentTime();
		 $user_folder_path = $this->getUsersSpecificFilesFolder($sent_from, "sent");
		 for($file = 0; $file < count($files["name"]); $file++)
		 {
			 if($files["name"][$file] != "")
			 {
				 $file_location = $user_folder_path ."/" .$files["name"][$file];
				 for($c = 0; $c < count($recipients); $c++)
			     {
			         $this->insertSentFileIntoTable($sent_from, $recipients[$c], $date_sent, $time_sent, $file_location);
				     $insertedFile = $this->getInsertedSentFile($sent_from, $recipients[$c], $date_sent, $time_sent, $file_location);
				     if(isset($insertedFile->file_id))
				     {
                         $insertedFile->file_size = round( filesize($files["tmp_name"][$file]) / 1024, 2) ."kb";
					     array_push($sentFiles, $insertedFile);
				     }
			     }
				 move_uploaded_file($files["tmp_name"][$file], $file_location);
			 }
		 }
		 return $sentFiles;
	 }
    
    public function getReceivedFile($file_id)
    {
        $received_file = new stdClass();
        $sql = "SELECT * FROM sent_files WHERE file_id = ?";
        $data = array($file_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        if($statement->rowCount() === 1)
        {
            $received_file = $statement->fetchObject();
        }
        return $received_file;
    }
	 
	 private function insertSentFileIntoTable($sent_from, $sent_to, $date_sent, $time_sent, $file_location)
	 {
		 $sql = "INSERT INTO sent_files (sent_from, sent_to, date_sent, time_sent, file_location) VALUES(?, ?, ?, ?, ?)";
		 $data = array($sent_from, $sent_to, $date_sent, $time_sent, $file_location);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 public function getSentFiles($cols_to_get = NULL, $cols_to_compare = NULL )
	 {
		 //construct the relevant sql statement:
		 $sql = "SELECT ";
		 if( is_null($cols_to_get) || count($cols_to_get) == 0 )
		 {
			 $sql .= "* ";
		 }
		 else
		 {
			 for($col = 0; $col < count($cols_to_get); $col++)
			 {
				 if($col != count($cols_to_get) - 1 )
				 {
					 $sql .= $cols_to_get[$col] .", ";
				 }
				 else
				 {
					 $sql .= $cols_to_get[$col] ." ";
				 }
			 }
		 }
		 
		 $sql .= "FROM sent_files";
		 if( is_null($cols_to_compare) || count($cols_to_compare) == 0 )
		 {
			 $sql .= "* FROM sent_files ";
		 }
		 else
		 {
			 for($col = 0; $col < count($cols_to_get); $col++)
			 {
				 if($col != count($cols_to_get) - 1 )
				 {
					 $sql .= $cols_to_get[$col] .", ";
				 }
				 else
				 {
					 $sql .= $cols_to_get[$col];
				 }
			 }
		 }
		 return $sql;
	 }
	 
	 public function fetchFilesSentByUser($user_id)
	 {
		 $sentFiles = array();
		 $sql = "SELECT * FROM sent_files 
		         INNER JOIN user_account ON user_account.user_id = sent_files.sent_to
		         WHERE sent_from = ?";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($sentFile = $statement->fetchObject())
		 {
			 $sentFile->file_name = basename($sentFile->file_location);
			 $sentFile->file_size = round( filesize($sentFile->file_location) / 1024, 2) ."kb";
			 array_push($sentFiles, $sentFile);
		 }
		 return $sentFiles;
	 }
	 
	 public function fetchFilesSentToUser($user_id)
	 {
		 $sentFiles = array();
		 $sql = "SELECT * FROM sent_files 
		         INNER JOIN user_account ON user_account.user_id = sent_files.sent_from
		         WHERE sent_to = ?";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($sentFile = $statement->fetchObject())
		 {
			 $sentFile->file_name = basename($sentFile->file_location);
			 $sentFile->file_size = round( filesize($sentFile->file_location) / 1024, 2) ."kb";
			 array_push($sentFiles, $sentFile);
		 }
		 return $sentFiles;
	 }
	 
	 private function getInsertedSentFile($sent_from, $sent_to, $date_sent, $time_sent, $file_location)
	 {
		 $sentFile = new stdClass();
		 $sql = "SELECT * FROM sent_files 
		      INNER JOIN user_account ON user_account.user_id = sent_files.sent_to
		      WHERE sent_from = ? AND sent_to = ? AND date_sent = ? AND time_sent = ? AND file_location = ?";
		 $data = array($sent_from, $sent_to, $date_sent, $time_sent, $file_location);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $sentFile = $statement->fetchObject();
			 $sentFile->file_name = basename($sentFile->file_location);
		 }
		 return $sentFile;
	 }
	 
	 public function getLatestIncomingFiles($user_id)
	 {
		 $incomingFiles = array();
		 $sql = "SELECT * FROM sent_files 
		         INNER JOIN user_account ON user_account.user_id = sent_files.sent_from
		         WHERE sent_to = ? AND received = 0";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($sentFile = $statement->fetchObject())
		 {
			 $sentFile->file_name = basename($sentFile->file_location);
			 $sentFile->file_size = round( filesize($sentFile->file_location) / 1024, 2) ."kb";
			 array_push($incomingFiles, $sentFile);
		 }
		 return $incomingFiles;
	 }
	 
	 private function getUsersSpecificFilesFolder($user_id, $folder_to_get = "received")
	 {
		 $filesFolder = "";
		 $sql = "SELECT user_id, CONCAT_WS(' ', first_name, last_name) AS user_name FROM user_account WHERE user_id = ?";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $recipient = $statement->fetchObject();
			 $filesFolder = "user_files/" .str_replace(" ", "_", strtolower($recipient->user_name)) ."_" .$recipient->user_id;
		 }
		 if($filesFolder != "")
		 {
			 switch($folder_to_get)
		     {
			     case "received":
				     $filesFolder .= "/received_files";
				 break;
				 case "sent":
				     $filesFolder .= "/sent_files";
				 break;
				 case "temp":
				     $filesFolder .= "/temporary_files";
				 break;
		     }
		 }
		 return $filesFolder;
	 }
	 
    private function getIdOfLastRoomCreated()
    {
        $room_id = 0;
        $sql = "SELECT room_id FROM rooms ORDER BY room_number DESC LIMIT 1";
        $statement = $this->makeStatement($this->db, $sql);
        if($statement->rowCount() == 1)
        {
            $room_id = $statement->fetchObject()->room_id;
        }
        return $room_id;
    }
    
    private function checkRoomIdExists($room_id)
    {
        $id_exists = false;
        $sql = "SELECT class_id FROM classes WHERE class_id = ?";
        $data = array($room_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        if($statement->rowCount() == 1)
        {
            $id_exists = true;
        }
        return $id_exists;
    }
    
    private function constructRoomId()
    {
        $room_id = $this->getIdOfLastRoomCreated();
        $this_room_id = $room_id + 1;
        while($this->checkRoomIdExists($this_room_id))
        {
            $this_room_id += 1;
        }
        return $this_room_id;
    }
    
	 private function saveRoom($room_name, $room_members, $date_created, $time_created, $created_by, $room_dp)
	 {
         $room_id = $this->constructRoomId();
		 $sql = "INSERT INTO rooms (room_id, room_name, room_members, date_created, time_created, created_by) VALUES (?, ?, ?, ?, ?, ?)";
		 $data = array($room_id, $room_name, $room_members, $date_created, $time_created, $created_by);
		 if( !is_null($room_dp) )
		 {
			 $sql = "INSERT INTO rooms (room_id, room_name, room_members, date_created, time_created, created_by, room_dp) VALUES (?, ?, ?, ?, ?, ?, ?)";
			 $room_dp_file = "roomlogos/" .$room_dp["name"];
			 $data = array($room_id, $room_name, $room_members, $date_created, $time_created, $created_by, $room_dp_file);
		 }
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 private function getTheJustSavedRoom($room_name, $room_members, $date_created, $time_created, $created_by)
	 {
		 $room = new stdClass();
		 $sql = "SELECT room_id, room_name, room_members, CONCAT_WS(' ', rooms.date_created, rooms.time_created) AS room_creation_timestamp, 
		                rooms.created_by, rooms.active, room_dp, user_id, CONCAT_WS(' ', first_name, last_name) AS room_creator_name
		         FROM rooms 
		         INNER JOIN user_account ON user_account.user_id = rooms.created_by
		         WHERE room_name = ? AND rooms.date_created = ? AND rooms.time_created = ? AND rooms.created_by = ?";
		 $data = array($room_name, $date_created, $time_created, $created_by);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $room = $statement->fetchObject();
			 $room->members = explode(",", $room->room_members);
			 $room->room_dp = $this->getRoomDisplayPicture("ALL_ROOMS", $room->room_dp);
		 }
		 return $room;
	 }
	 
	 public function createRoom($room_name, $room_members, $created_by, $display_picture = NULL)
	 {
		 $date_created = $this->getCurrentDate();
		 $time_created = $this->getCurrentTime();
		 $room_members_string = "";
		 for($m = 0; $m < count($room_members); $m++)
		 {
			 if($m == 0)
			 {
				 $room_members_string .= $room_members[$m];
			 }
			 else
			 {
				 $room_members_string .= "," .$room_members[$m];
			 }
		 }
		 $this->saveRoom($room_name, $room_members_string, $date_created, $time_created, $created_by, $display_picture);
		 if( !is_null($display_picture) )
		 {
			 //save the room display picture:
		     $thumbSizes = array(
			     array(40, 40), array(30, 30));
	         $this->createThumbNailImageAndSaveOriginal($display_picture, "roomlogos/", "roomthumblogos/", "thumb", $thumbSizes, false);
		 }
		 return $this->getTheJustSavedRoom($room_name, $room_members_string, $date_created, $time_created, $created_by);
	 }
	 
    private function isRoomPinnedForUser($user_id, $room_id)
    {
        $pinned = 0;
        $sql = "SELECT user_id, room_id FROM pinned_rooms WHERE user_id = ? AND room_id = ?";
        $data = array($user_id, $room_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        if($statement->rowCount() == 1)
        {
            $pinned = 1;
        }
        return $pinned;
    }
    
	 //any room created by any contact of current user should be fetched as current user's rooms:
	 //of course rooms created by this user him/herself are included too:
	 public function fetchUserRooms($user_contacts)
	 {
		 $user_rooms = array();
		 array_push($user_contacts, $_SESSION['userid']);
		 for($c = 0; $c < count($user_contacts); $c++)
		 {
			 $rooms_by_user = $this->getRoomsCreatedByParticularUser($user_contacts[$c], $_SESSION['userid']);
			 $user_rooms = array_merge($user_rooms, $rooms_by_user);
			 
		 }
		 usort($user_rooms, "sortUserRoomsFunction");
		 return $user_rooms;
	 }
	 
	 public function getRoomsCreatedByParticularUser($user_id, $current_user)
	 {
		 $rooms = array();
		 $sql = "SELECT rooms.room_id, room_name, room_members, CONCAT_WS(' ', rooms.date_created, rooms.time_created) AS room_creation_timestamp, 
		                rooms.created_by, rooms.active, room_dp, user_account.user_id, CONCAT_WS(' ', first_name, last_name) AS room_creator_name
		         FROM rooms
                 INNER JOIN user_account ON user_account.user_id = rooms.created_by
		         WHERE rooms.created_by = ?";
		 $data = array($user_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 while($room = $statement->fetchObject())
		 {
             $room->pinned = $this->isRoomPinnedForUser($current_user, $room->room_id);
             $room->room_type = "user_created";
			 $room->members = explode(",", $room->room_members);
			 $room->room_dp = $this->getRoomDisplayPicture("ALL_ROOMS", $room->room_dp);
			 array_push($rooms, $room);
		 }
		 return $rooms;
	 }
	 
	 public function getRoomDisplayPicture($display_context, $image_file_path = NULL)
	 {
		 $display_picture_path = "roomthumblogos/default_room_logo.jpg";
		 if( !is_null($image_file_path) && $image_file_path != "")
		 {
			 $image_file_extension = pathinfo($image_file_path)['extension'];
			 $image_file_name = str_replace(".", "", basename($image_file_path, $image_file_extension));
			 $display_picture_name = $image_file_name ."_thumb.".$image_file_extension;
			 switch($display_context)
		     {
			     case "SIDEBAR_ROOMS":
				     $thumb_picture_location = "roomthumblogos/thumbs_30x30/" .$display_picture_name;
			     case "ALL_ROOMS":
				     $thumb_picture_location = "roomthumblogos/thumbs_40x40/" .$display_picture_name;
			     break;
			     default:
				     $thumb_picture_location = $display_picture_path;
			     break;
		     }
			
			 if(file_exists($thumb_picture_location) && is_file($thumb_picture_location))
			 {
				 $display_picture_path = $thumb_picture_location;
			 }
		 }
		 return $display_picture_path;
	 }
	 
	 private function updateRoomMembers($room_id, $room_members)
	 {
		 $sql = "UPDATE rooms SET room_members = ? WHERE room_id = ?";
		 $data = array($room_members, $room_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
    
     public function pinRoom($room_id, $user_id)
	 {
         $sql = "INSERT INTO pinned_rooms (user_id, room_id, pinned) VALUES (?, ?, ?)";
		 $data = array($user_id, $room_id, 1);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
    
     public function unpinRoom($room_id, $user_id)
	 {
		 $sql = "DELETE FROM pinned_rooms WHERE user_id = ? AND room_id = ?";
		 $data = array($user_id, $room_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 return true;
	 }
	 
	 private function fetchRoomMemberString($room_id)
	 {
		 $room_members = "";
		 $sql = "SELECT room_members FROM rooms WHERE room_id = ?";
		 $data = array($room_id);
		 $statement = $this->makeStatement($this->db, $sql, $data);
		 if($statement->rowCount() === 1)
		 {
			 $room_members = $statement->fetchObject()->room_members;
		 }
		 return $room_members;
	 }
	 
	 public function joinRoom($room_id, $user_id)
	 {
		 $room_member_string = $this->fetchRoomMemberString($room_id);
		 $room_member_string .= "," .$user_id;
		 $this->updateRoomMembers($room_id, $room_member_string);
	 }
	 
	 public function leaveRoom($room_id, $user_id)
	 {
		 $room_member_string = $this->fetchRoomMemberString($room_id);
		 $room_members = explode(",", $room_member_string);
		 $new_members = array();
		 for($m = 0; $m < count($room_members); $m++)
		 {
			 if($room_members[$m] != $user_id)
			 {
				 array_push($new_members, $room_members[$m]);
			 }
		 }
		 $new_members_string = $this->constructStringFromArray($new_members, ",");
		 $this->updateRoomMembers($room_id, $new_members_string);
         $this->unpinRoom($room_id, $user_id);
	 }
	 
	 private function constructStringFromArray($array, $separator)
	 {
		 $string = "";
		 for($e = 0; $e < count($array); $e++)
		 {
			 if($e == 0)
			 {
				 $string = $array[$e];
			 }
			 else
			 {
				 $string .= $separator.$array[$e];
			 }
		 }
		 return $string;
	 }
    
    public function getRoomDetails($room_id)
    {
        $room = new stdClass();
        $sql = "SELECT room_id, room_name, room_members, CONCAT_WS(' ', rooms.date_created, rooms.time_created) AS room_creation_timestamp, 
		                rooms.created_by, rooms.active, room_dp, user_id, CONCAT_WS(' ', first_name, last_name) AS room_creator_name
		         FROM rooms
		         INNER JOIN user_account ON user_account.user_id = rooms.created_by
		         WHERE rooms.room_id = ?";
        $data = array($room_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        if($statement->rowCount() == 1)
        {
            $room = $statement->fetchObject();
            $room->members = explode(",", $room->room_members);
            $room->room_dp = $this->getRoomDisplayPicture("ALL_ROOMS", $room->room_dp);
        }
        return $room;
    }
}


function sortUserRoomsFunction($room_a, $room_b)
{
	 if($room_a->room_creation_timestamp == $room_b->room_creation_timestamp) return 0;
	 return ($room_a->room_creation_timestamp > $room_b->room_creation_timestamp) ? -1 : 1;
}
?>
