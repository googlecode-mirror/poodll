<?php  // $Id: playviewcontroller.php,v 1.0 2009/02/03 12:23:36 justin Exp $
/**
 * This page handles actions and backend set related to the play tabview of a poodllpairwork instance
 *
 * @author
 * @version $Id: playviewcontroller.php,v 1.0 2009/02/03 12:23:36 justin Exp $
 * @package poodllpairwork
 **/



	
		
		//check to see if we are A or B
		$studentalias="";
		switch($pairwork->sessiontype){
			case SESSIONTYPE_IP:
				break;
			case SESSIONTYPE_USERNAME:
				if ($pairmap = get_record("poodllpairwork_usermap", "username", $USER->username, "course", $course->id)) {
						$studentalias = $pairmap->role;
					}					
				break;
			case SESSIONTYPE_MANUAL:
			default:
				if (array_key_exists( 'studentalias', $_GET)){
					$studentalias = $_GET['studentalias'];
				}		
		
		}
		


?>
