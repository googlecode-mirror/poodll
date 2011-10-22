<?php // $Id: index.php,v 1.7 2007/09/03 12:23:36 justin Exp $
/**
 * This page lists all the instances of poodllpairwork in a particular course
 *
 * @author
 * @version $Id: index.php,v 1.7 2007/09/03 12:23:36 justin Exp $
 * @package poodllpairwork
 **/


    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "poodllpairwork", "view all", "index.php?id=$course->id", "");


/// Get all required strings poodllpairwork

    $strpairworks = get_string("modulenameplural", "poodllpairwork");
    $strpairwork  = get_string("modulename", "poodllpairwork");


/// Print the header

    $navlinks = array();
    $navlinks[] = array('name' => $strpairworks, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple("$strpairworks", "", $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $pairworks = get_all_instances_in_course("poodllpairwork", $course)) {
        notice("There are no PoodLL Pairworks", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($pairworks as $pairwork) {
        if (!$pairwork->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$pairwork->coursemodule\">$pairwork->name</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$pairwork->coursemodule\">$pairwork->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($pairwork->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
