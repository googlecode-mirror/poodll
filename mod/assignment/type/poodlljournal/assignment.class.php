<?php // $Id: assignment.class.php,v 1.46.2.8 2008/07/24 11:11:58 skodak Exp $
require_once($CFG->libdir.'/formslib.php');

//to call the recorder from media resource lib: justin 20090105
require_once($CFG->libdir . '/poodllresourcelib.php');


	define("COMMENTSDELIM" , "GTSEFF");
	define("PARTSDELIM" , "ODBTRT");
/**
 * Extend the base assignment class for assignments where you upload a single file
 *
 */
class assignment_poodlljournal extends assignment_base {
	
	//global COMMENTSDELIM = "GTSEFF";
	//global PARTSDELIM = "ODBTRT";


    function assignment_poodlljournal($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'poodlljournal';
    }

    function view() {

        global $USER;

        $edit  = optional_param('edit', 0, PARAM_BOOL);
        $saved = optional_param('saved', 0, PARAM_BOOL);

        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        require_capability('mod/assignment:view', $context);

        $submission = $this->get_submission();

        //Guest can not submit nor edit an assignment (bug: 4604)
        if (!has_capability('mod/assignment:submit', $context)) {
            $editable = null;
        } else {
            $editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
        }
        $editmode = ($editable and $edit);

        if ($editmode) {
            //guest can not edit or submit assignment
            if (!has_capability('mod/assignment:submit', $context)) {
                print_error('guestnosubmit', 'assignment');
            }
        }

        add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

		/// prepare form and process submitted data
        //Justin:20090518 added some paramaters for dealing with when we render form, about line#738
		//$mform = new mod_assignment_poodlljournal_edit_form();
		$mform = new mod_assignment_poodlljournal_edit_form(null, array("cm"=>$this->cm,"assignment"=>$this->assignment));

        $defaults = new object();
        $defaults->id = $this->cm->id;
        if (!empty($submission)) {
            if ($this->usehtmleditor) {
                $options = new object();
                $options->smiley = false;
                $options->filter = false;
				//we only show a new box, can't edit old data Justin
                //$defaults->text   = format_text($submission->data1, $submission->data2, $options);
				$defaults->text   = "";
				
                $defaults->format = FORMAT_HTML;
            } else {
                //we only show a new box, can't editold data Justin
				//$defaults->text   = $submission->data1;
				$defaults->text   = "";
				
                $defaults->format = $submission->data2;
            }
        }
        $mform->set_data($defaults);

        if ($mform->is_cancelled()) {
            redirect('view.php?id='.$this->cm->id);
        }

        if ($data = $mform->get_data()) {      // No incoming data?
            if ($editable && $this->update_submission($data)) {
                //TODO fix log actions - needs db upgrade
                $submission = $this->get_submission();
                add_to_log($this->course->id, 'assignment', 'upload',
                        'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                $this->email_teachers($submission);
                //redirect to get updated submission date and word count
                redirect('view.php?id='.$this->cm->id.'&saved=1');
            } else {
                // TODO: add better error message
                notify(get_string("error")); //submitting not allowed!
            }
        }

/// print header, etc. and display form if needed
        if ($editmode) {
            $this->view_header(get_string('poodlljournaladdto', 'assignment_poodlljournal'));
        } else {
            $this->view_header();
        }

        $this->view_intro();

	//This is a journal we do not really need to show "submit by" dates	
     //   $this->view_dates();

        if ($saved) {
            notify(get_string('submissionsaved', 'assignment'), 'notifysuccess');
        }
		
		//The button to add an entry
		if (!$editmode && $editable) {
                echo "<div style='text-align:center'>";
                print_single_button('view.php', array('id'=>$this->cm->id,'edit'=>'1'),
                        get_string('poodlljournaladdto', 'assignment_poodlljournal'));
                echo "</div>";
            }

        if (has_capability('mod/assignment:submit', $context)) {
            if ($editmode) {
                print_box_start('generalbox', 'poodlljournal');
                $mform->display();
				print_box_end();
            }
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
						            print_user_picture($person, $this->course->id, $person->picture);
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
						        echo format_text($feedback);
						        echo '</div>';
						        echo '</tr>';

						        echo '</table>';
							
							}//end of if sizeof > 2
					}//end of for each
				}//end of if submission not empty
			
				//edit mode or not we show old submissions
				print_box_start('generalbox boxwidthwide boxaligncenter', 'poodlljournal');
							
				
                if ($submission) {
					//Justin: we do not need this, since submissions are shown above
                    //echo format_text($submission->data1, $submission->data2);
                } else if (!has_capability('mod/assignment:submit', $context)) { //fix for #4604
                    echo '<div style="text-align:center">'. get_string('guestnosubmit', 'assignment').'</div>';
                } else if ($this->isopen()){    //fix for #4206
                    echo '<div style="text-align:center">'.get_string('emptysubmission', 'assignment').'</div>';
                }
				print_box_end();		
            

        }

		//Justin: we do not need this, since feedback is now displayed inline
        //$this->view_feedback();

        $this->view_footer();
    }

	
    /**
     *  Display a single submission, ready for grading on a popup window
     * Justin: I just lifted this from assignments/lib.php because  I needed to show the conversation entries properly.beneath the feedback form.
     * This default method prints the teacher info and submissioncomment box at the top and
     * the student info and submission at the bottom.
     * This method also fetches the necessary data in order to be able to
     * provide a "Next submission" button.
     * Calls preprocess_submission() to give assignment type plug-ins a chance
     * to process submissions before they are graded
     * This method gets its arguments from the page parameters userid and offset
     */
    function display_submission($extra_javascript = '') {

        global $CFG;
        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->libdir.'/tablelib.php');

        $userid = required_param('userid', PARAM_INT);
        $offset = required_param('offset', PARAM_INT);//offset for where to start looking for student.

        if (!$user = get_record('user', 'id', $userid)) {
            error('No such user!');
        }

        if (!$submission = $this->get_submission($user->id)) {
            $submission = $this->prepare_new_submission($userid);
        }
        if ($submission->timemodified > $submission->timemarked) {
            $subtype = 'assignmentnew';
        } else {
            $subtype = 'assignmentold';
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, array($user->id));
        $disabled = $grading_info->items[0]->grades[$userid]->locked || $grading_info->items[0]->grades[$userid]->overridden;

    /// construct SQL, using current offset to find the data of the next student
        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $context    = get_context_instance(CONTEXT_MODULE, $cm->id);

        /// Get all ppl that can submit assignments

        $currentgroup = groups_get_activity_group($cm);
        if ($users = get_users_by_capability($context, 'mod/assignment:submit', 'u.id', '', '', '', $currentgroup, '', false)) {
            $users = array_keys($users);
        }

        // if groupmembersonly used, remove users who are not in any group
        if ($users and !empty($CFG->enablegroupings) and $cm->groupmembersonly) {
            if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                $users = array_intersect($users, array_keys($groupingusers));
            }
        }

        $nextid = 0;

        if ($users) {
            $select = 'SELECT u.id, u.firstname, u.lastname, u.picture, u.imagealt,
                              s.id AS submissionid, s.grade, s.submissioncomment,
                              s.timemodified, s.timemarked,
                              COALESCE(SIGN(SIGN(s.timemarked) + SIGN(s.timemarked - s.timemodified)), 0) AS status ';
            $sql = 'FROM '.$CFG->prefix.'user u '.
                   'LEFT JOIN '.$CFG->prefix.'assignment_submissions s ON u.id = s.userid
                                                                      AND s.assignment = '.$this->assignment->id.' '.
                   'WHERE u.id IN ('.implode(',', $users).') ';

            if ($sort = flexible_table::get_sql_sort('mod-assignment-submissions')) {
                $sort = 'ORDER BY '.$sort.' ';
            }

            if (($auser = get_records_sql($select.$sql.$sort, $offset+1, 1)) !== false) {
                $nextuser = array_shift($auser);
            /// Calculate user status
                $nextuser->status = ($nextuser->timemarked > 0) && ($nextuser->timemarked >= $nextuser->timemodified);
                $nextid = $nextuser->id;
            }
        }

        print_header(get_string('feedback', 'assignment').':'.fullname($user, true).':'.format_string($this->assignment->name));

        /// Print any extra javascript needed for saveandnext
        echo $extra_javascript;

        ///SOme javascript to help with setting up >.>

        echo '<script type="text/javascript">'."\n";
        echo 'function setNext(){'."\n";
        echo 'document.getElementById(\'submitform\').mode.value=\'next\';'."\n";
        echo 'document.getElementById(\'submitform\').userid.value="'.$nextid.'";'."\n";
        echo '}'."\n";

        echo 'function saveNext(){'."\n";
        echo 'document.getElementById(\'submitform\').mode.value=\'saveandnext\';'."\n";
        echo 'document.getElementById(\'submitform\').userid.value="'.$nextid.'";'."\n";
        echo 'document.getElementById(\'submitform\').saveuserid.value="'.$userid.'";'."\n";
        echo 'document.getElementById(\'submitform\').menuindex.value = document.getElementById(\'submitform\').grade.selectedIndex;'."\n";
        echo '}'."\n";

        echo '</script>'."\n";
        echo '<table cellspacing="0" class="feedback '.$subtype.'" >';

        ///Start of teacher info row

        echo '<tr>';
        echo '<td class="picture teacher">';
        
		//Edited by justin 20081003: otherwise regardlss of which teacher is logged in , 
		//first teacher to write in journal would be shown, 
		//if ($submission->teacher) {
		if (false) {
            $teacher = get_record('user', 'id', $submission->teacher);
        } else {
            global $USER;
            $teacher = $USER;
        }
        print_user_picture($teacher, $this->course->id, $teacher->picture);
        echo '</td>';
        echo '<td class="content">';
        echo '<form id="submitform" action="submissions.php" method="post">';
        echo '<div>'; // xhtml compatibility - invisiblefieldset was breaking layout here
        echo '<input type="hidden" name="offset" value="'.($offset+1).'" />';
        echo '<input type="hidden" name="userid" value="'.$userid.'" />';
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        echo '<input type="hidden" name="mode" value="grade" />';
        echo '<input type="hidden" name="menuindex" value="0" />';//selected menu index

        //new hidden field, initialized to -1.
        echo '<input type="hidden" name="saveuserid" value="-1" />';

        if ($submission->timemarked) {
            echo '<div class="from">';
            echo '<div class="fullname">'.fullname($teacher, true).'</div>';
            echo '<div class="time">'.userdate($submission->timemarked).'</div>';
            echo '</div>';
        }
      //if we are grading journals 
		if ($this->assignment->grade > 0){
		
			echo '<div class="grade"><label for="menugrade">'.get_string('grade').'</label> ';
	        choose_from_menu(make_grades_menu($this->assignment->grade), 'grade', $submission->grade, get_string('nograde'), '', -1, false, $disabled);
	        echo '</div>';

	        echo '<div class="clearer"></div>';
	        echo '<div class="finalgrade">'.get_string('finalgrade', 'grades').': '.$grading_info->items[0]->grades[$userid]->str_grade.'</div>';
	        echo '<div class="clearer"></div>';

	        if (!empty($CFG->enableoutcomes)) {
	            foreach($grading_info->outcomes as $n=>$outcome) {
	                echo '<div class="outcome"><label for="menuoutcome_'.$n.'">'.$outcome->name.'</label> ';
	                $options = make_grades_menu(-$outcome->scaleid);
	                if ($outcome->grades[$submission->userid]->locked) {
	                    $options[0] = get_string('nooutcome', 'grades');
	                    echo $options[$outcome->grades[$submission->userid]->grade];
	                } else {
	                    choose_from_menu($options, 'outcome_'.$n.'['.$userid.']', $outcome->grades[$submission->userid]->grade, get_string('nooutcome', 'grades'), '', 0, false, false, 0, 'menuoutcome_'.$n);
	                }
	                echo '</div>';
	                echo '<div class="clearer"></div>';
	            }
	        }
		//if we are not grading journals	
		}else{	
			echo '<input type="hidden" name="grade" value="-1" />';
		}


        $this->preprocess_submission($submission);

        if ($disabled) {
            echo '<div class="disabledfeedback">'.$grading_info->items[0]->grades[$userid]->str_feedback.'</div>';

        } else {
		
			//---------------Justin Video Message start 20090105-------------------
			if ($CFG->filter_poodll_journal_video || $CFG->filter_poodll_journal_audio) {
				echo '<a href="#" onclick="document.getElementById(\'teacherrecorder\').style.display=\'block\';">Record Audio/Video</a>';
				echo "<div id='teacherrecorder' style='display: none'>";
					//$rtmplink = "rtmp://{$CFG->rtmp}";	
	        			$rtmplink=$CFG->poodll_media_server;
					//$filename='poodlljournal/' . $this->assignment->id . $submission->userid . time(). rand() . '.flv';
					$filename='moddata/assignment/' . $this->assignment->id .  '/' . $submission->userid . '/teacher_'  . time(). rand() . '.flv';
					$mediadata= fetch_teachersrecorder($filename, "mediafilename");
					echo $mediadata;
					echo '<input type="hidden" value="" id="mediafilename" name="mediafilename" />';
				echo "</div>";			
			}
				
			//---------------Video Message end 20090105---------------------
		
			//Justin: We never edit old submissions so we show a blank area in the feedback box
            //print_textarea($this->usehtmleditor, 14, 58, 0, 0, 'submissioncomment', $submission->submissioncomment, $this->course->id);			
			print_textarea($this->usehtmleditor, 14, 58, 0, 0, 'submissioncomment', "", $this->course->id);
            if ($this->usehtmleditor) {
                echo '<input type="hidden" name="format" value="'.FORMAT_HTML.'" />';
            } else {
                echo '<div class="format">';
                choose_from_menu(format_text_menu(), "format", $submission->format, "");
                helpbutton("textformat", get_string("helpformatting"));
                echo '</div>';
            }

        }

        $lastmailinfo = get_user_preferences('assignment_mailinfo', 1) ? 'checked="checked"' : '';

        ///Print Buttons in Single View
		

        echo '<input type="hidden" name="mailinfo" value="0" />';
        echo '<input type="checkbox" id="mailinfo" name="mailinfo" value="1" '.$lastmailinfo.' /><label for="mailinfo">'.get_string('enableemailnotification','assignment').'</label>';
        echo '<div class="buttons">';
        echo '<input type="submit" name="submit" value="'.get_string('savechanges').'" onclick = "document.getElementById(\'submitform\').menuindex.value = document.getElementById(\'submitform\').grade.selectedIndex" />';
        echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />';
        //if there are more to be graded.
        if ($nextid) {
            echo '<input type="submit" name="saveandnext" value="'.get_string('saveandnext').'" onclick="saveNext()" />';
            echo '<input type="submit" name="next" value="'.get_string('next').'" onclick="setNext();" />';
        }
        echo '</div>';
        echo '</div></form>';

        $customfeedback = $this->custom_feedbackform($submission, true);
        if (!empty($customfeedback)) {
            echo $customfeedback;
        }

        echo '</td></tr>';

        ///End of teacher info row, Start of student info row	
		
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
						        //echo '<table cellspacing="0" class="feedback">';							
						        echo '<tr>';
						        echo '<td class="left picture">';
						        if ($person) {
						            print_user_picture($person, $this->course->id, $person->picture);
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
						        echo '<td class="content" colspan="2">';						     
						        echo '<div class="comment">';
						        echo format_text($feedback);
						        echo '</div>';
								echo '</td>';
						        echo '</tr>';

							
							}//end of if sizeof > 2
					}//end of for each

				}//end of if submission not empty

        ///End of student info row

        echo '</table>';

        if (!$disabled and $this->usehtmleditor) {
            use_html_editor();
        }

        print_footer('none');
    }


    /*
     * Display the assignment dates
     */
    function view_dates() {
        global $USER, $CFG;

        if (!$this->assignment->timeavailable && !$this->assignment->timedue) {
            return;
        }

        print_simple_box_start('center', '', '', 0, 'generalbox', 'dates');
        echo '<table>';
        if ($this->assignment->timeavailable) {
            echo '<tr><td class="c0">'.get_string('availabledate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timeavailable).'</td></tr>';
        }
        if ($this->assignment->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timedue).'</td></tr>';
        }
        $submission = $this->get_submission($USER->id);
        if ($submission) {
            echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
            echo '    <td class="c1">'.userdate($submission->timemodified);
        /// Decide what to count
            if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')</td></tr>';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')</td></tr>';
            }
        }
        echo '</table>';
        print_simple_box_end();
    }

	  /*
     * Process an incoming submission
     */
    function update_submission($data) {
        global $CFG, $USER;

        $submission = $this->get_submission($USER->id, true);

        $update = new object();
        $update->id           = $submission->id;
		
		//justin: 20090519
		//a into moodle form system. to get the filename of a recording, if we have one
		//if we have one we tack the audio player to the user submission.
		$filename = optional_param('saveflvvoice', '', PARAM_RAW);
		if ($filename){
				if ($CFG->filter_poodll_journal_video) {
					$data->text .= '<BR />{POODLL:type=video,path=' . $filename . '}';
				}else{
					$data->text .= '<BR />{POODLL:type=audio,path=' . $filename . '}';
				}
		}
		
		//Justin: add old data to new submission with a line break
		if ($this->usehtmleditor) {
			
			
			
			$update->data1 = $data->text . PARTSDELIM . $USER->id . PARTSDELIM . time() . 
									COMMENTSDELIM . addslashes($submission->data1);
			
			
			
		}else{
			$update->data1        =  $data->text . "\r\n" . addslashes($submission->data1);
		}
		
        $update->data2        = $data->format;
        $update->timemodified = time();

        if (!update_record('assignment_submissions', $update)) {
            return false;
        }

        $submission = $this->get_submission($USER->id);
        $this->update_grade($submission);
        return true;
    }
    


    function print_student_answer($userid, $return=false){
        global $CFG;
        if (!$submission = $this->get_submission($userid)) {
            return '';
        }
        $output = '<div class="files">'.
                  '<img src="'.$CFG->pixpath.'/f/html.gif" class="icon" alt="html" />'.
                  link_to_popup_window ('/mod/assignment/type/poodlljournal/file.php?id='.$this->cm->id.'&amp;userid='.
                  $submission->userid, 'file'.$userid, shorten_text(trim(strip_tags(format_text($submission->data1,$submission->data2))), 15), 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';
                  return $output;
    }

    function print_user_files($userid, $return=false) {
        global $CFG;

        if (!$submission = $this->get_submission($userid)) {
            return '';
        }

        $output = '<div class="files">'.
                  '<img align="middle" src="'.$CFG->pixpath.'/f/html.gif" height="16" width="16" alt="html" />'.
                  link_to_popup_window ('/mod/assignment/type/poodlljournal/file.php?id='.$this->cm->id.'&amp;userid='.
                  $submission->userid, 'file'.$userid, shorten_text(trim(strip_tags(format_text($submission->data1,$submission->data2))), 15), 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';

        ///Stolen code from file.php

        print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
    /// Decide what to count
        if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
            echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')';
        } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
            echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')';
        }
        print_simple_box_end();
        print_simple_box(format_text($submission->data1, $submission->data2), 'center', '100%');

        ///End of stolen code from file.php

        if ($return) {
            //return $output;
        }
        //echo $output;
    }

    function preprocess_submission(&$submission) {
		
	
	
        if ($this->assignment->var1 && empty($submission->submissioncomment)) {  // comment inline
            if ($this->usehtmleditor) {
                // Convert to html, clean & copy student data to teacher
                $submission->submissioncomment = format_text($submission->data1, $submission->data2);
                $submission->format = FORMAT_HTML;
            } else {
                // Copy student data to teacher
                $submission->submissioncomment = $submission->data1;
                $submission->format = $submission->data2;
            }
        }
    }
	
	
	    /**
     *  Display and process the submissions 
 *  We need to do this to add teachers submission to students feedback. It is a bad bad hack, but it looks ok	 
     */ 
    function process_feedback() {                 
                
        global $USER, $CFG;
         
        if (!$feedback = data_submitted()) {      // No incoming data?
            return false;
        }     
                          
        ///For save and next, we need to know the userid to save, and the userid to go...
        ///We use a new hidden field in the form, and set it to -1. If it's set, we use this
        ///as the userid to store...
        if ((int)$feedback->saveuserid !== -1){
            $feedback->userid = $feedback->saveuserid;
        }       
        
        if (!empty($feedback->cancel)) {          // User hit cancel button
            return false;
        }       
        
        $newsubmission = $this->get_submission($feedback->userid, true);  // Get or make one
          
		 //if we are grading journals 
		$newsubmission->grade      = $feedback->grade;
        $newsubmission->submissioncomment    = $feedback->submissioncomment;
        $newsubmission->format     = $feedback->format;
        $newsubmission->teacher    = $USER->id;
        $newsubmission->mailed     = 0;       // Make sure mail goes out (again, even)
        $newsubmission->timemarked = time();
		
					 
			//---------------Justin Video Message start 20090105-------------------	
		if(!empty($_POST['mediafilename'])){
			$mediafile = $_POST['mediafilename'];
				  
			if ($mediafile) 
			{						
				if ($CFG->filter_poodll_journal_video) {
				  $newtext = '{POODLL:type=video,path='.$mediafile.'}';	
				}else{
				  $newtext = '{POODLL:type=audio,path='.$mediafile.'}';	
				}
			  
			    $feedback->submissioncomment=  $newtext . '<BR />' . $feedback->submissioncomment;
			}
		}
		//---------------Video Message end 20090105---------------------
		//tack teachers comment onto students
		$newsubmission->data1 = $feedback->submissioncomment .  PARTSDELIM . $USER->id . PARTSDELIM . time() .
									COMMENTSDELIM .  addslashes($newsubmission->data1);
    
	
	   //unset($newsubmission->data1);
	   unset($newsubmission->data2);  // Don't need to update this.
		

        if (! update_record('assignment_submissions', $newsubmission)) {
            return false;
        }
        
		// trigger grade event(lib.php)
		assignment_update_grades($this->assignment, $feedback->userid);
		
        add_to_log($this->course->id, 'assignment', 'update grades', 
                   'submissions.php?id='.$this->assignment->id.'&user='.$feedback->userid, $feedback->userid, $this->cm->id);   
        
        return $newsubmission;
                 
    }   


    function setup_elements(&$mform) {
        global $CFG, $COURSE;

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'resubmit', get_string("allowresubmit", "assignment"), $ynoptions);
        $mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));
        $mform->setDefault('resubmit', 0);

        $mform->addElement('select', 'emailteachers', get_string("emailteachers", "assignment"), $ynoptions);
        $mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
        $mform->setDefault('emailteachers', 0);

        $mform->addElement('select', 'var1', get_string("commentinline", "assignment"), $ynoptions);
        $mform->setHelpButton('var1', array('commentinline', get_string('commentinline', 'assignment'), 'assignment'));
        $mform->setDefault('var1', 0);
		

    }

}

class mod_assignment_poodlljournal_edit_form extends moodleform {
    function definition() {
		global $USER, $CFG;
        $mform =& $this->_form;	
		

        // visible elements
        $mform->addElement('htmleditor', 'text', get_string('submission', 'assignment'), array('cols'=>60, 'rows'=>15));
        $mform->setType('text', PARAM_RAW); // to be cleaned before display
        $mform->setHelpButton('text', array('reading', 'writing', 'richtext'), false, 'editorhelpbutton');
        $mform->addRule('text', get_string('required'), 'required', null, 'client');

        $mform->addElement('format', 'format', get_string('format'));
        $mform->setHelpButton('format', array('textformat', get_string('helpformatting')));
		
		//Justin: added a Voice Recorder to the journal 20090518
		//Justin: 20090519 :to allow us prefix our red5 data.
		//We have passed data into the variable $customdata
		//This is info we passed in at around #57		
		if ($CFG->filter_poodll_journal_video || $CFG->filter_poodll_journal_audio) {
			$linktext = '<a href="#" onclick="document.getElementById(\'poodllmediarecorder\').style.display=\'block\';">Record PoodLL Media</a>';
			$divstarttext= "<div id='poodllmediarecorder' style='display: none'>";
                if ($CFG->filter_poodll_journal_video) {
			//$recordertext= fetchSimpleVideoRecorder('assignment/' . $this->_customdata['cm']->id . '/' . $USER->id . '/' . time() . rand() , $USER->id);					
			$recordertext= fetchSimpleVideoRecorder('assignment/' . $this->_customdata['assignment']->id  , $USER->id);					
                } else {
			//$recordertext= fetchSimpleAudioRecorder('assignment/' . $this->_customdata['cm']->id . '/' . $USER->id . '/' . time() . rand() , $USER->id);					
			$recordertext= fetchSimpleAudioRecorder('assignment/' . $this->_customdata['assignment']->id  , $USER->id);					
                }

			$divendtext= "</div>";
			$mediadata = $linktext . $divstarttext . $recordertext . $divendtext;				
								
			//Add the PoodllAudio recorder. Theparams are the def filename and the DOM id of the filename html field to update
			$mform->addElement('static', 'description', get_string('poodllmediarecorder', 'assignment_poodlljournal'),$mediadata);
		}


        // hidden params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // buttons
        $this->add_action_buttons();
    }
}

?>
