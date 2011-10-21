<?PHP // $Id: index.php,v 1.1 2003/09/30 02:45:19 moodler Exp $

/// This page lists all the instances of poodllflashcard in a particular course
/// Replace poodllflashcard with the name of your module

    require_once("../../config.php");
    require_once("lib.php");

	//edited Justin 2008/08/08
	$id = required_param('id', PARAM_INT);   // course
    //require_variable($id);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "poodllflashcard", "view all", "index.php?id=$course->id", "");


/// Get all required strings

    $strflashcards = get_string("modulenameplural", "poodllflashcard");
    $strflashcard  = get_string("modulename", "poodllflashcard");


/// Print the header
    $streditquestions = isteacheredit($course->id)
                        ? "<form target=\"_parent\" method=\"get\" "
                           ." action=\"$CFG->wwwroot/mod/quiz/edit.php\">"
                           ."<input type=\"hidden\" name=\"courseid\" "
                           ." value=\"$course->id\" />"
                           ."<input type=\"submit\" "
                           ." value=\"".get_string("editquestions", "quiz")."\" /></form>"

                        : "";

    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }

    print_header("$course->shortname: $strflashcards", "$course->fullname", "$navigation $strflashcards", "", "", true, $streditquestions, navmenu($course));

/// Get all the appropriate data

    if (! $flashcards = get_all_instances_in_course("poodllflashcard", $course)) {
        notice("There are no PoodLL Flashcards", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("CENTER", "LEFT");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("CENTER", "LEFT", "LEFT", "LEFT");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("LEFT", "LEFT", "LEFT");
    }

    foreach ($flashcards as $flashcard) {
        if (!$flashcard->visible) {
            //Show dimmed if the mod is hidden
            $link = "<A class=\"dimmed\" HREF=\"view.php?id=$flashcard->coursemodule\">$flashcard->name</A>";
        } else {
            //Show normal if the mod is visible
            $link = "<A HREF=\"view.php?id=$flashcard->coursemodule\">$flashcard->name</A>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($flashcard->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<BR>";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
