<?php  // $Id: view.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

    /** 
    * This page prints a particular instance of a poodllflashcard
    * 
    * @package mod-poodllflashcard
    * @category mod
    * @author Gustav Delius
    * @contributors Valery Fremaux
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

    require_once("../../config.php");
    require_once("{$CFG->dirroot}/mod/poodllflashcard/lib.php");
    require_once("{$CFG->dirroot}/mod/poodllflashcard/locallib.php");

    $id = optional_param('id', '', PARAM_INT);    // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);     // poodllflashcard ID
    $view = optional_param('view', 'checkdecks', PARAM_ACTION);     // view
    $page = optional_param('page', '', PARAM_ACTION);     // page
    $action = optional_param('what', '', PARAM_ACTION);     // command

    if ($id) {
        if (! $cm = get_record('course_modules', 'id', $id)) {
            error('Course Module ID was incorrect');
        }
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }
        if (! $flashcard = get_record('poodllflashcard', 'id', $cm->instance)) {
            error("Course module is incorrect");
        }
    } else {
        if (! $flashcard = get_record('poodllflashcard', 'id', $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $flashcard->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("poodllflashcard", $flashcard->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    add_to_log($course->id, 'poodllflashcard', 'view', "view.php?id=$cm->id", "$flashcard->name");

/// Print the page header

    $strflashcards = get_string('modulenameplural', 'poodllflashcard');
    $strflashcard  = get_string('modulename', 'poodllflashcard');
    if (!function_exists('build_navigation')){
        if ($course->category) {
            $navigation = "<a href=\"{$CFG->wwwroot}/course/view.php?id=$course->id\">$course->shortname</a> ->";
        }
        print_header("$course->shortname: $flashcard->name", "$course->fullname",
                 "$navigation <a href=index.php?id=$course->id>$strflashcards</a> -> $flashcard->name", 
                  '', '', true, update_module_button($cm->id, $course->id, $strflashcard), 
                  navmenu($course, $cm));
    } else {
        $navlinks = array(array('name' => $flashcard->name, 'link' => '', 'type' => 'title'));
        $navigation = build_navigation($navlinks);
        print_header("$course->shortname: $flashcard->name", "$course->fullname", $navigation);
    }

/// non visible trap for timerange (security)
    if (!has_capability('moodle/course:viewhiddenactivities', $context) && !$cm->visible){
        error("This page cannot be accessed while module is not visible");
    }

/// non manager trap for timerange

    if (!has_capability('mod/poodllflashcard:manage', $context)){
        $now = time();
        if (($flashcard->starttime != 0 && $now < $flashcard->starttime) || ($flashcard->endtime != 0 && $now > $flashcard->endtime)){
            error(get_string('outoftimerange', 'poodllflashcard'));
        }
    }    

/// loads customisation styles

    $localstyle = "/{$course->id}/moddata/poodllflashcard/{$flashcard->id}/poodllflashcard.css";
    if (file_exists("{$CFG->dataroot}/{$localstyle}")){
        if ($CFG->slasharguments) {
            $localstyleurl = $CFG->wwwroot.'/file.php/'.$localstyle;
        } else {
            if ($CFG->slasharguments){
                $localstyleurl = $CFG->wwwroot.'/file.php?file='.$localstyle;
            } else {
                $localstyleurl = $CFG->wwwroot.'/file.php'.$localstyle;
            }
        }
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$localstyleurl}\" />";
    } else {
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$CFG->wwwroot}/mod/poodllflashcard/poodllflashcard.css\" />";
    }

/// Determine the current tab

    switch($view){
		//I hacked this because I did not want students to do leitner : Justin
        //case 'checkdecks' : $currenttab = 'play'; break;
		case 'checkdecks' : $currenttab = 'freeplay'; break;
		
        case 'play' : $currenttab = 'play'; break;
        case 'freeplay' : $currenttab = 'freeplay'; break;
        case 'summary' : $currenttab = 'summary'; break;
        case 'edit' : $currenttab = 'edit'; break;
        default : $currenttab = 'play';
    }

/// print tabs
//Justin : I hacked this, because I did not want students to do leitner:
    if (!preg_match("/summary|freeplay|play|checkdecks|edit/", $view)) $view = 'checkdecks';
	//justins hack
    //$tabname = get_string('leitnergame', 'poodllflashcard');
    //$row[] = new tabobject('play', "view.php?id={$cm->id}&amp;view=checkdecks", $tabname);
    $tabname = get_string('freegame', 'poodllflashcard');
    $row[] = new tabobject('freeplay', "view.php?view=freeplay&amp;id={$cm->id}", $tabname);
	
	
    if (has_capability('mod/poodllflashcard:manage', $context)){
        $tabname = get_string('teachersummary', 'poodllflashcard');
        $row[] = new tabobject('summary', "view.php?view=summary&amp;id={$cm->id}&amp;page=byusers", $tabname);
        $tabname = get_string('edit', 'poodllflashcard');
        $row[] = new tabobject('edit', "view.php?view=edit&amp;id={$cm->id}", $tabname);
    }
    $tabrows[] = $row;
    
    $activated = array();

/// print second line

    if ($view == 'summary'){
        switch($page){
            case 'bycards' : {
                $currenttab = 'bycards';
                $activated[] = 'summary'; 
                break;
            }
            default : {
                $currenttab = 'byusers';
                $activated[] = 'summary';
            }
        }

        $tabname = get_string('byusers', 'poodllflashcard');
        $row1[] = new tabobject('byusers', "view.php?id={$cm->id}&amp;view=summary&amp;page=byusers", $tabname);
        $tabname = get_string('bycards', 'poodllflashcard');
        $row1[] = new tabobject('bycards', "view.php?id={$cm->id}&amp;view=summary&amp;page=bycards", $tabname);
        $tabrows[] = $row1;
    }

    print_tabs($tabrows, $currenttab, null, $activated);

/// print summary

    if (!empty($flashcard->summary)) {
        print_box_start();
        echo format_text($flashcard->summary, $flashcard->summaryformat, NULL, $course->id);
        print_box_end();
    }

/// print active view

    switch ($view){
        case 'summary' : 
            if (!has_capability('mod/poodllflashcard:manage', $context)){
                redirect("view.php?view=checkdecks&amp;id={$cm->id}");
            }
            if ($page == 'bycards'){
                include "cardsummaryview.php";
            } else {
                include "usersummaryview.php";
            }
            break;
        case 'edit' : 
            if (!has_capability('mod/poodllflashcard:manage', $context)){
                redirect("view.php?view=checkdecks&amp;id={$cm->id}");
            }
            include "editview.php";
            break;
        case 'freeplay' :
            include "freeplayview.php";
            break;
        case 'play' :
            include "playview.php";
            break;
        default :
			//Justin : I hacked this, because I did not want students to do leitner:
            //include "checkview.php";
			include "freeplayview.php";
            break;
    }

/// Finish the page

    print_footer($course);
?>
