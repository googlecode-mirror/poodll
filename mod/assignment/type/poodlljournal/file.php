<?php  // $Id: file.php,v 1.6 2006/08/31 08:51:09 toyomoyo Exp $

    require("../../../../config.php");
    require("../../lib.php");
    require("assignment.class.php");
	
 
    $id     = required_param('id', PARAM_INT);      // Course Module ID
    $userid = required_param('userid', PARAM_INT);  // User ID

    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $assignment = get_record("assignment", "id", $cm->instance)) {
        error("Assignment ID was incorrect");
    }

    if (! $course = get_record("course", "id", $assignment->course)) {
        error("Course is misconfigured");
    }

    if (! $user = get_record("user", "id", $userid)) {
        error("User is misconfigured");
    }

    require_login($course->id, false, $cm);

    if (($USER->id != $user->id) && !has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        error("You can not view this assignment");
    }

    if ($assignment->assignmenttype != 'poodlljournal') {
        error("Incorrect assignment type");
    }

    $assignmentinstance = new assignment_poodlljournal($cm->id, $assignment, $cm, $course);

    if ($submission = $assignmentinstance->get_submission($user->id)) {
        print_header(fullname($user,true).': '.$assignment->name);

        print_simple_box_start('center', '', '', '', 'generalbox', 'dates');
        echo '<table>';
        if ($assignment->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($assignment->timedue).'</td></tr>';
        }
        echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
        echo '    <td class="c1">'.userdate($submission->timemodified);
        /// Decide what to count
            if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')</td></tr>';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')</td></tr>';
            }
        echo '</table>';
        print_simple_box_end();

       // print_simple_box(format_text($submission->data1, $submission->data2), 'center', '100%');
	   if (!empty($submission)){
				$comments = explode(COMMENTSDELIM ,$submission->data1);
					foreach($comments as $comment){
						$commentParts = explode(PARTSDELIM, $comment);
						if (sizeof($commentParts)>2){
								$feedback=$commentParts[0];
								$personId = $commentParts[1];
								$comment_date= $commentParts[2];
								
								
								$person = get_record('user', 'id', $personId);
								/// Print the feedback
						        //print_heading(get_string('feedbackfromteacher', 'assignment', $this->course->teacher)); 
						       echo '<table cellspacing="0" class="feedback">';

						        echo '<tr>';
						        echo '<td class="left picture">';
						        if ($person) {
						            print_user_picture($person, $course->id, $person->picture);
						        }
						        echo '</td>';
						        echo '<td class="topic">';
						        echo '<div class="from">';
						        if ($person) {
						            echo '<div class="fullname">'.fullname($person).'</div>';
						        }
						        echo '<div class="time">'.userdate($comment_date).'</div>';
						        echo '</div>';
						        echo '</td>';
						        echo '</tr>';

						        echo '<tr>';
						        echo '<td class="left side">&nbsp;</td>';
						        echo '<td class="content">';
						        echo '<div class="clearer"></div>';

						        echo '<div class="comment">';
						        echo $feedback;
						        echo '</div>';
						        echo '</tr>';

						        echo '</table>';
							
							}//end of if sizeof > 2
					}//end of for each
				}//end of if submission not empty
       

	   close_window_button();
        print_footer('none');
    } else {
        print_string('emptysubmission', 'assignment');
    }

?>
