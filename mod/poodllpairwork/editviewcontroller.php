<?php
/** 
* a controller for the edit view
* 
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*
*/


	

	

//add all unassigned onlineusers from this course to the pair session.
switch ($action){
	case 'addsession':
	
		//lets fetch an pairs we might have.
			$pairs = fetch_pairs($course->id);	
			//fetch all users online from the course containing this module, that have not yet been put into a pair session
			$users = fetch_unassigned_users($ttl,$course->id,$context);
			
			
			//lets use ourlist of users to create some pairs and reg indb
			shuffle($users);		
			while (!empty($users) && count($users)>1){
				$userone = array_pop($users);
				$usertwo = array_pop($users);
				$pairs[]=create_pair($userone,$usertwo,$course->id);
			}
			break;


	//Clear an entire pair session
	case 'clearsession':

		if (!delete_records_select('poodllpairwork_usermap', " course = $course->id ")){
	        error ("Could not delete PoodLL Pairwork session");
	    }  
		
		//fetch all users online from the course containing this module, that have not yet been put into a pair session
		$users = fetch_unassigned_users($ttl,$course->id,$context);
		//lets init our oairs array.
		$pairs = array();
		break;

	//clear an individual pair from a session
	case  'clearpair':
		if( $pairid != '0'){
			$pairid = trim($pairid);
			delete_records_select('poodllpairwork_usermap', " username = '$pairid' AND course = $course->id ");
			delete_records_select('poodllpairwork_usermap', " partnername = '$pairid' AND course = $course->id");
			//if (!(delete_records_select('poodllpairwork_usermap', " username = '$pairid' ") &&
			//			delete_records_select('poodllpairwork_usermap', " partnername = '$pairid' "))){
			 //       error ("Could not delete pair");
		    //}  
		}
		//fetch our updated pairs array
		$pairs = fetch_pairs($course->id);
		//fetch all users online from the course containing this module, that have not yet been put into a pair session
		$users = fetch_unassigned_users($ttl,$course->id,$context);
		break;

	case 'refreshsession':	
	default:
		//fetch our pairs array
		$pairs = fetch_pairs($course->id);
		//fetch all users online from the course containing this module, that have not yet been put into a pair session
		$users = fetch_unassigned_users($ttl,$course->id,$context);

	
	}




?>
