<?php

/**
* internal library of functions and constants for module poodllpairwork
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
*
*/

/**
* Includes and requires
*/
define('SESSIONTYPE_MANUAL', 0); 
define('SESSIONTYPE_IP', 1); 
define('SESSIONTYPE_USERNAME', 2); 
define('LOGGEDIN_PERIOD',10);

//Fetch our list of pairs
//Figure out if we have a pair session registered
//if use load up the pairs array
function fetch_pairs( $courseid){
global $CFG;
	$pairs = array();
	$pairelements = array();
	$selectsql = "SELECT * FROM {$CFG->prefix}poodllpairwork_usermap WHERE course = $courseid";
	
	if ($ppairs = get_records_sql($selectsql)) {
		foreach ($ppairs as $pair) {
			$pairelement = array();
			$pairelement['username']=$pair->username;
			$pairelement['partnername']=$pair->partnername;
			$pairelement['fullname']=$pair->fullname;
			$pairelement['role']=$pair->role;
			$pairelements[$pairelement['username']]=$pairelement;
		}		
		while (!empty($pairelements) && count($pairelements)>1){
			$elementone = array_shift($pairelements);
			$elementtwo = $pairelements[$elementone['partnername']];
			unset($pairelements[$elementone['partnername']]);
			$pairs[]=array($elementone['role']=>$elementone,$elementtwo['role']=>$elementtwo);
		}
	}
	return $pairs;

}

//Fetch our list of pairs
//Figure out if we have a pair session registered
//if use load up the pairs array
function fetch_xmlpairs( $courseid){
global $CFG;
	$pairs = fetch_pairs($courseid);

	$xml_output = "<pairsets>\n";
	foreach ($pairs as $pair){
		$pairstreamname =  rand(1000000, 9999999);
		$xml_output .= "\t<pair name='$pairstreamname' dirty='false'>\n";
		foreach ($pair as $pelement){
			$user = get_record('user', 'username', $pelement['username']);
			$xml_output .= "\t\t<pairelement username='" . $pelement['username'] . 
								"' showname='" .  $pelement['fullname'] .
								"' pictureurl='" . fetch_user_picture($user,35) . 
								"' />\n";
		}
		$xml_output .= "\t</pair>\n";
	}
	

	$xml_output .= "</pairsets>";

	//echo $xml_output; 
	
	
	return $xml_output;

}
//codify any dangerous xml characters. 
function xml_clean_string($text){
			$text = str_replace("&", "&", $text);
			$text = str_replace("<", "<", $text);
			$text = str_replace(">", "&gt;", $text);
			$text = str_replace("\"", "&quot;", $text);
			return $text;
}

//Fetch the users for this course who have accessed the site withinthe number of mins passed in
function fetch_unassigned_users($minstoshowusers, $courseid, $context){
global $CFG;
	
	$timetoshowusers = $minstoshowusers * 60; //Seconds default
	$timefrom = 100 * floor((time()-$timetoshowusers) / 100); // Round to nearest 100 seconds for better query cache

 $selectsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, max(ul.timeaccess) as lastaccess ";
            $from = "FROM {$CFG->prefix}user_lastaccess ul,
                          {$CFG->prefix}user u ";
            $where =  "WHERE ul.timeaccess > $timefrom
                       AND u.id = ul.userid
                       AND ul.courseid = $courseid 
					   AND u.username not in 
					   (SELECT p.username FROM {$CFG->prefix}poodllpairwork_usermap p WHERE p.course = $courseid)
						AND u.username not in 
						 (SELECT p.partnername FROM {$CFG->prefix}poodllpairwork_usermap p WHERE p.course = $courseid)";
            $order = "ORDER BY lastaccess DESC ";
        
        
        $groupby = "GROUP BY u.id, u.username, u.firstname, u.lastname, u.picture ";
        
        $SQL = $selectsql . $from . $where . $groupby . $order;
		$students = array(); 
		if ($users =get_records_sql($SQL)){
			//this puts students first and if we end up with an odd number of students , we add a teacher.
			if (count($users)>0){
		
				//these throw errors when noone is online , but Ido not know a better way ..
				//we probably need to rewrite the sort function
				$students = sort_by_roleassignment_authority($users, $context,array(5));
				$nonstudents = sort_by_roleassignment_authority($users, $context, array(1,2,3,4));
				if ((count($students) % 2) > 0 && count($nonstudents)>0){
					$students[] = array_pop($nonstudents);
				}
			}
		}
		//We need to create pairs, so we return our students array, not the user array which contains all.
		return $students;
}
/**
* creates a pair
*/
function create_pair($userone, $usertwo, $courseid){
			$pair = array();
			$pair["A"] = create_pair_element($userone, $usertwo, $courseid, "A");
			$pair["B"] = create_pair_element($usertwo, $userone, $courseid, "B");
			return $pair;
	}
	
/*
*
* Creates each element of a pair
*
*/	
	function create_pair_element($userone, $usertwo, $courseid, $role){
   	
			
			//first register pair element in DB.
			//if we can't do this, all is nought.
			$dbpair = array();
			$dbpair['course']=$courseid;
			$dbpair['username']=$userone->username;
			$dbpair['fullname']=fullname($userone);
			$dbpair['partnername']=$usertwo->username;
			$dbpair['role']=$role;			
			if (!insert_record('poodllpairwork_usermap', $dbpair)){
                    error ("Could not insert $userone->username | $usertwo->username");
                }
				
			//return the pair element	
			$pairelement = array();	
			$pairelement['username']=$userone->username;
			$pairelement['partnername']=$usertwo->username;
			$pairelement['fullname']=fullname($userone);
			$pairelement['role']=$role;
			return $pairelement;
  
	}
	
?>
