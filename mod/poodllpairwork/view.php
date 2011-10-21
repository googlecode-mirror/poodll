<?php  // $Id: view.php,v 1.6 2007/09/03 12:23:36 justin Exp $
/**
 * This page prints a particular instance of poodllpairwork
 *
 * @author
 * @version $Id: view.php,v 1.6 2007/09/03 12:23:36 justin Exp $
 * @package poodllpairwork
 **/

/// (Replace poodllpairwork with the name of your module)

    require_once("../../config.php");
    require_once("lib.php");
	require_once("locallib.php");
	//added: justin 20090716
	require_once($CFG->libdir . '/poodllresourcelib.php');
	
	global $USER;

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // poodllpairwork ID
	$ttl  = optional_param('sessionttl', LOGGEDIN_PERIOD, PARAM_INT);  // Session TTL
	$pairid = optional_param('pairid', 0, PARAM_RAW);  // an individual pairs ID;
	$view = optional_param('view', 'play', PARAM_ACTION);     // view
	$action = optional_param('what', '', PARAM_ACTION);     // command

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }

        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }

        if (! $pairwork = get_record("poodllpairwork", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $pairwork = get_record("poodllpairwork", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $pairwork->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("poodllpairwork", $pairwork->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
	$context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_login($course->id);

    add_to_log($course->id, "poodllpairwork", "view", "view.php?id=$cm->id", "$pairwork->id");


	//If this is request for XML data, 
	//do that first and don't return html
	if ($view=="xmldata"){
		header("Content-type: text/xml");
		echo "<?xml version=\"1.0\"?>\n";
		echo fetch_xmlpairs($course->id);
		return;
	}


/// Print the page header
    $strpairworks = get_string("modulenameplural", "poodllpairwork");
    $strpairwork  = get_string("modulename", "poodllpairwork");

    $navlinks = array();
    $navlinks[] = array('name' => $strpairworks, 'link' => "index.php?id=$course->id", 'type' => 'activity');
    $navlinks[] = array('name' => format_string($pairwork->name), 'link' => '', 'type' => 'activityinstance');

    $navigation = build_navigation($navlinks);

    print_header_simple(format_string($pairwork->name), "", $navigation, "", "", true,
                  update_module_button($cm->id, $course->id, $strpairwork), navmenu($course, $cm));
//Setup and Print the tab header
/// Determine the current tab

    switch($view){
        case 'play' : $currenttab = 'play'; break;
        case 'edit' : $currenttab = 'edit'; break;
        default : $currenttab = 'play';
    }

/// print tabs
    if (!preg_match("/play|edit/", $view)) $view = 'play';
    $tabname = get_string('letsplay', 'poodllpairwork');
    $row[] = new tabobject('play', "view.php?id={$cm->id}&amp;view=play", $tabname);
    
    if (has_capability('mod/poodllpairwork:manage', $context)){
        $tabname = get_string('letsedit', 'poodllpairwork');
		$row[] = new tabobject('edit', "view.php?view=edit&amp;id={$cm->id}", $tabname);
    }
    $tabrows[] = $row;    
    $activated = array();
	
	//add a second tabrow like this
	//$tabrows[] = $row2;
	
    print_tabs($tabrows, $currenttab, null, $activated);



//call the appropriate page to show
switch ($view){
        case 'edit' : 
            if (!has_capability('mod/poodllpairwork:manage', $context)){
                redirect("view.php?view=play&amp;id={$cm->id}");
            }else{
				   include "editview.php";
			}
            break;
        case 'play' : 
			include "playview.php";
		}//end of switch case
				  
				  

/// Finish the page
    print_footer($course);
?>
