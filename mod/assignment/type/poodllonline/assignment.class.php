<?php // $Id: assignment.class.php,v 1.46.2.6 2008/04/15 03:40:09 moodler Exp $
require_once($CFG->libdir.'/formslib.php');

//Added Justin 2009/06/11 For printing to PDF

//Get our poodll resource handling lib
require_once($CFG->libdir . '/poodllresourcelib.php');


//some constants for the type of online poodll assignment
define('OM_REPLYTEXTONLY',0);
define('OM_REPLYVOICEONLY',1);
define('OM_REPLYVOICETHENTEXT',2);
define('OM_REPLYVIDEOONLY',3);
define('OM_REPLYVIDEOTHENTEXT',4);
define('OM_REPLYTALKBACK',5);
define('OM_FEEDBACKTEXT',0);
define('OM_FEEDBACKTEXTVOICE',1);
define('OM_FEEDBACKTEXTVIDEO',2);
define('HTML_FORMAT',1);
define('TCPPDF_OLD',0);

/**
 * Extend the base assignment class 
 *
 */
class assignment_poodllonline extends assignment_base {

    function assignment_poodllonline($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {	
	
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'poodllonline';
    }

    function view() {

        global $CFG, $USER;

        $edit  = optional_param('edit', 0, PARAM_BOOL);
        $saved = optional_param('saved', 0, PARAM_BOOL);
		$print  = optional_param('print', 0, PARAM_BOOL);
		
        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        require_capability('mod/assignment:view', $context);

        $submission = $this->get_submission();
		
		//We need to add an extra field to the submissions table, for feedback using video or audio
		//we check if it exists here, and if not we add it. Justin 20100324
		if($submission){
				if (!column_type('assignment_submissions', 'poodllfeedback')){
					// add field to store media comments (audio or video) filename to students submissions
			        $sql= "ALTER TABLE " . $CFG->prefix ."assignment_submissions ADD poodllfeedback TEXT";
					$result = execute_sql($sql);
				}
		}
			
		//Justin	
		//Are we printing this or not
		if ($print){
			if (TCPPDF_OLD){
				require_once($CFG->libdir . '/tcpdf/tcpdf.php');
			}else{
				require_once($CFG->libdir . '/newtcpdf/tcpdf.php');
			}

			
			 $pdf = new tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
			// remove default header/footer
			//old version of tcppdf
			if (TCPPDF_OLD){
				$pdf->print_header = false;
				$pdf->print_footer = false;
			}else{
			//new version of tcppdf
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false); 
			}


			//set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);


			//set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); 	
			$pdf->setFont('freeserif','',10);
			

			
			//make page
			$pdf->AddPage();
			
			//prepare html content
			 $options = new object();
             $options->smiley = false;
             $options->filter = false;
			 $strHtml = format_text($submission->data1, FORMAT_HTML, $options);
			 
			 
			//print the thing
			$pdf->writeHTML($strHtml,true,0,true,0); 
			//The I is for inline, meaning tell the browser to shopw not download it.
			$pdf->output('document.pdf', 'I');
			//$pdf->output();
			return;
		}
		

        //Guest can not submit nor edit an assignment (bug: 4604)
        if (!has_capability('mod/assignment:submit', $context)) {
            $editable = null;
        } else {
            $editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
        }
		
		//modify Justin 20090305, we don't want to add this extra step for users.
		//If they can edit, and they haven't submitted anything, then lets just show the form.
		//If they have submitted something, lets give them an extra step if ytthey want to submit
		//to protect accidental overwrite of their submission.
       // $editmode = ($editable and $edit);
	    $editmode = ($editable and (!$submission || $edit));

        if ($editmode) {
            //guest can not edit or submit assignment
            if (!has_capability('mod/assignment:submit', $context)) {
                print_error('guestnosubmit', 'assignment');
            }
        }

        add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

	/// prepare form and process submitted data
	//load it with some info it needs to determine the params for chosho recorder.
		//for voice then text, we need to know if we already have voice or not
		if(empty($submission)){
				$mediapath="";
			}else{
				$mediapath=$submission->data2;
		}
        $mform = new mod_assignment_poodllonline_edit_form(null, array("cm"=>$this->cm,"assignment"=>$this->assignment,"mediapath"=>$mediapath));

        $defaults = new object();
        $defaults->id = $this->cm->id;
        if (!empty($submission)) {
			//we always use html editor: Justin 20090225
            //if ($this->usehtmleditor) {
			if (true) {
                $options = new object();
                $options->smiley = false;
                $options->filter = false;

                $defaults->text   = format_text($submission->data1, FORMAT_HTML, $options);
                $defaults->format = FORMAT_HTML;
            } else {
                $defaults->text   = $submission->data1;
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
            $this->view_header(get_string('editmysubmission', 'assignment'));
        } else {
            $this->view_header();
        }

        $this->view_intro();

        $this->view_dates();
		
	

        if ($saved) {
            notify(get_string('submissionsaved', 'assignment'), 'notifysuccess');
        }

        if (has_capability('mod/assignment:submit', $context)) {
            print_simple_box_start('center', '70%', '', 0, 'generalbox', 'poodllonline');
            if ($editmode) {
					
				if ($submission) {				
				 
					 //Show our  students answer box
					 echo get_string('mysubmission', 'assignment_poodllonline');
					 print_simple_box_start('center', '50%', '', 0, 'generalbox', 'mysubmission');
				
				//check if we need media output
					switch($this->assignment->var3){
						
						case OM_REPLYVOICEONLY:
							//format and echo text that our Audio filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up								
							//echo format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);
							break;						

						case OM_REPLYVIDEOONLY:
							//format and echo text that our Video filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up								
							//echo format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);							
							break;

						
						case OM_REPLYVOICETHENTEXT:						
							//format and echo text that our Audio filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up
							//echo format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);	
							break;

						case OM_REPLYVIDEOTHENTEXT:						
							//format and echo text that our Video filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up
							//echo format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);
							break;
			
					}
					
					//check if we need text output	
					switch($this->assignment->var3){
						case OM_REPLYVOICETHENTEXT:
						case OM_REPLYVIDEOTHENTEXT:	
							if(empty($submission->data1)){
								break;
							}else{
								echo "<br />";
							}
							
						case OM_REPLYTEXTONLY:
						default:	
							echo format_text($submission->data1, FORMAT_HTML);
					}


					//Close our students answer box
					print_simple_box_end();
				}

			
					$mform->display();				
            } else {
                if ($submission) {
					
					//Show our  students answer box
					echo get_string('mysubmission', 'assignment_poodllonline');
					print_simple_box_start('center', '50%', '', 0, 'generalbox', 'mysubmission');
					
					switch($this->assignment->var3){
						
						case OM_REPLYVOICEONLY:
							//format and echo text that our Audio filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up								
							//echo format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML);	
							echo format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);								
							break;
							

						case OM_REPLYVIDEOONLY:
							//format and echo text that our Video filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up								
							//echo format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML);	
							echo format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);							
							break;

						
						case OM_REPLYVOICETHENTEXT:						
							//format and echo text that our Audio filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up
							//echo format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);
							break;

						case OM_REPLYVIDEOTHENTEXT:						
							//format and echo text that our Video filter will pick and show in a player
							//needs to be formatted as html for filter to pick it up
							//echo format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML);
							echo format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML);
							break;
						
						

					}
					
					
					//check if we need text output	
					switch($this->assignment->var3){
						case OM_REPLYVOICETHENTEXT:
						case OM_REPLYVIDEOTHENTEXT:	
							if(empty($submission->data1)){
								break;
							}else{
								echo "<br />";
							}
							
						case OM_REPLYTEXTONLY:
						default:	
							echo format_text($submission->data1, FORMAT_HTML);
					}
					
					
					
					//Close out students answer box
					print_simple_box_end();
                    
                
				
				
				
				} else if (!has_capability('mod/assignment:submit', $context)) { //fix for #4604
                    echo '<div style="text-align:center">'. get_string('guestnosubmit', 'assignment').'</div>';
                } else if ($this->isopen()){    //fix for #4206
                    echo '<div style="text-align:center">'.get_string('emptysubmission', 'assignment').'</div>';
                }
            }
            print_simple_box_end();
            if (!$editmode && $editable) {
                echo "<div style='text-align:center'>";
                print_single_button('view.php', array('id'=>$this->cm->id,'edit'=>'1'),
                        get_string('editmysubmission', 'assignment'));	
                echo "</div>";
            }
		
			//show a print buttonif it is text only and not edit mode	
			if ($this->assignment->var3 == OM_REPLYTEXTONLY && !$editmode){
					echo "<br /><div style='text-align:center'>";					
					echo "<a href='view.php?id=" . $this->cm->id . "&print=1' target='_new'>" . get_string('printthissubmission', 'assignment_poodllonline') . 								"</a>";
									
					//The target tag is ignored by print_single_button so not using it
					//print_single_button('view.php', array('id'=>$this->cm->id,'print'=>'1'),get_string('printthissubmission', 'assignment_poodllonline'),'get','_new');		
					echo "</div>";
		
			}//end of if printable		

        }//end of if can submit

        $this->view_feedback();

        $this->view_footer();
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
                echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, FORMAT_HTML))).')</td></tr>';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, FORMAT_HTML))).')</td></tr>';
            }
        }
        echo '</table>';
        print_simple_box_end();
    }

    function update_submission($data) {
        global $CFG, $USER;

        $submission = $this->get_submission($USER->id, true);
		
		//a crazy hack into moodle form system. to get both the recorder to update the filename in a form field
		//and to have moodle get that field
		$filename = optional_param('saveflvvoice', '', PARAM_RAW);

        $update = new object();
        $update->id           = $submission->id;
		if (!empty($data->text)){
			$update->data1        = $data->text;
		}else{
			$update->data1 = "";
		}
		
		//update media field with data that our moodle audio filter will pick up
		if (!empty($filename)){
			//$update->data2         = $data->saveflvvoice;
			$update->data2         = $filename;
			
		}
		//We just use html
        //$update->data2        = $data->format;
        $update->timemodified = time();

        if (!update_record('assignment_submissions', $update)) {
            return false;
        }

        $submission = $this->get_submission($USER->id);
        $this->update_grade($submission);
        return true;
    }

	
	
	//WE override this method only so we can 
	//show the students any media feedback the teacher may have entered
	  /**
	    /**
     * Display the feedback to the student
     *
     * This default method prints the teacher picture and name, date when marked,
     * grade and teacher submissioncomment.
     *
     * @param $submission object The submission object or NULL in which case it will be loaded
     */
    function view_feedback($submission=NULL) {
        global $USER, $CFG;
        require_once($CFG->libdir.'/gradelib.php');

        if (!has_capability('mod/assignment:submit', $this->context, $USER->id, false)) {
            // can not submit assignments -> no feedback
            return;
        }

        if (!$submission) { /// Get submission for this assignment
            $submission = $this->get_submission($USER->id);
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, $USER->id);
        $item = $grading_info->items[0];
        $grade = $item->grades[$USER->id];
		
        
		if ($grade->hidden or $grade->grade === false) { // hidden or error		
            return;
        }

        if ($grade->grade === null and empty($grade->str_feedback)) {   /// Nothing to show yet
            return;
        }

        $graded_date = $grade->dategraded;
        $graded_by   = $grade->usermodified;

    /// We need the teacher info
        if (!$teacher = get_record('user', 'id', $graded_by)) {
            error('Could not find the teacher');
        }

    /// Print the feedback
        print_heading(get_string('feedbackfromteacher', 'assignment', $this->course->teacher)); // TODO: fix teacher string

        echo '<table cellspacing="0" class="feedback">';

        echo '<tr>';
        echo '<td class="left picture">';
        if ($teacher) {
            print_user_picture($teacher, $this->course->id, $teacher->picture);
        }
        echo '</td>';
        echo '<td class="topic">';
        echo '<div class="from">';
        if ($teacher) {
            echo '<div class="fullname">'.fullname($teacher).'</div>';
        }
        echo '<div class="time">'.userdate($graded_date).'</div>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        echo '<div class="grade">';
		//modify Justin 20081001 Hide grades from users
		if (!$grade->hidden ){
			//echo "hidden " . $grade->hidden ;
			//echo get_string("grade").': '.$grade->str_long_grade;
		}
        echo '</div>';
        echo '<div class="clearer"></div>';
		//display our media comment, as video or voice, depending on the assignment settings
		//if somehow the feedback is set to text only, and yet we have a media comment, presumably the teacher
		//changed the assignment settings after havinf submitted a  poodllfeedback.
		//in that case we default to video. (Should we default to showing nothing?)
		if (!empty($submission->poodllfeedback)){
			echo '<div class="comment">';
			if ($this->assignment->var4 == OM_FEEDBACKTEXTVOICE){
				//echo format_text("{FMS:VOICE=" . $submission->poodllfeedback . "}",FORMAT_HTML);
				echo format_text('{POODLL:type=audio,path='.	$submission->poodllfeedback .',protocol=rtmp}', FORMAT_HTML);
			}else{
				//echo format_text("{FMS:VIDEO=" . $submission->poodllfeedback . "}",FORMAT_HTML);
				echo format_text('{POODLL:type=video,path='.	$submission->poodllfeedback .',protocol=rtmp}', FORMAT_HTML);
			}
	        echo '</div>';		
		}
        echo '<div class="comment">';
        echo $grade->str_feedback;
        echo '</div>';
        echo '</tr>';

        echo '</table>';
    }

	//WE override this method only so we can 
	//add a recorder that allows us to reply in audio/video
	//it would be easier to update assignment/lib.php 
	//then we could do this for all assignments	
	  /**
     *  Display a single submission, ready for grading on a popup window
     *
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
                              s.id AS submissionid, s.grade, s.submissioncomment,s.poodllfeedback,
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
        if ($submission->teacher) {
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


        $this->preprocess_submission($submission);

        if ($disabled) {
            echo '<div class="disabledfeedback">'.$grading_info->items[0]->grades[$userid]->str_feedback.'</div>';

        } else {
			//---------------Justin Video Message start 20090105-------------------
			//if our feedback is audio or video, show a link to the recorder
			if ($this->assignment->var4 == OM_FEEDBACKTEXTVIDEO){
				echo '<a href="#" onclick="document.getElementById(\'teacherrecorder\').style.display=\'block\';">Record Audio/Video</a>';
				echo "<div id='teacherrecorder' style='display: none'>";
					//$rtmplink = "rtmp://{$CFG->rtmp}";	
	        			$rtmplink=$CFG->poodll_media_server;
					if (!empty($submission->poodllfeedback)){
						$filename=$submission->poodllfeedback;
					}else{
						//$filename='onlinemedia/' . $this->assignment->id . $submission->userid . time(). rand() . '.flv';
						$filename='moddata/assignment/' . $this->assignment->id . '/' . $submission->userid . '/teacher_'  . time(). rand() . '.flv';
					}
					$mediadata= fetch_teachersrecorder($filename, "mediafilename");
					echo $mediadata;
					echo '<input type="hidden" value="" id="mediafilename" name="mediafilename" />';
				echo "</div>";			
			}else if ($this->assignment->var4 == OM_FEEDBACKTEXTVOICE){
				echo '<a href="#" onclick="document.getElementById(\'teacherrecorder\').style.display=\'block\';">Record Audio/Video</a>';
				echo "<div id='teacherrecorder' style='display: none'>";
					//$rtmplink = "rtmp://{$CFG->rtmp}";	
	        			$rtmplink=$CFG->poodll_media_server;
					if (!empty($submission->poodllfeedback)){
						$filename=$submission->poodllfeedback;
					}else{
						//$filename='onlinemedia/' . $this->assignment->id . $submission->userid . time(). rand() . '.flv';
						$filename='';
					}
					$mediadata= fetchSimpleAudioRecorder('assignment/' . $this->assignment->id  ,$submission->userid, "mediafilename",$filename);
					echo $mediadata;
					echo '<input type="checkbox" value="" id="mediafilename" name="mediafilename" />';
				echo "</div>";	
			
			}
			//---------------Video Message end 20090105---------------------
            print_textarea($this->usehtmleditor, 14, 58, 0, 0, 'submissioncomment', $submission->submissioncomment, $this->course->id);
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
        echo '<tr>';
        echo '<td class="picture user">';
        print_user_picture($user, $this->course->id, $user->picture);
        echo '</td>';
        echo '<td class="topic">';
        echo '<div class="from">';
        echo '<div class="fullname">'.fullname($user, true).'</div>';
        if ($submission->timemodified) {
            echo '<div class="time">'.userdate($submission->timemodified).
                                     $this->display_lateness($submission->timemodified).'</div>';
        }
        echo '</div>';
        $this->print_user_files($user->id);
        echo '</td>';
        echo '</tr>';

        ///End of student info row

        echo '</table>';

        if (!$disabled and $this->usehtmleditor) {
            use_html_editor();
        }

        print_footer('none');
    }

	//We override this so that our media feedback
	//will be appended to our text feednack: Justin 20090323
	    /**
     *  Process teacher feedback submission
     *
     * This is called by submissions() when a grading even has taken place.
     * It gets its data from the submitted form.
     * @return object The updated submission object
     */
    function process_feedback() {
        global $CFG, $USER;
        require_once($CFG->libdir.'/gradelib.php');

        if (!$feedback = data_submitted()) {      // No incoming data?
            return false;
        }

        ///For save and next, we need to know the userid to save, and the userid to go
        ///We use a new hidden field in the form, and set it to -1. If it's set, we use this
        ///as the userid to store
        if ((int)$feedback->saveuserid !== -1){
            $feedback->userid = $feedback->saveuserid;
        }

        if (!empty($feedback->cancel)) {          // User hit cancel button
            return false;
        }

        $grading_info = grade_get_grades($this->course->id, 'mod', 'assignment', $this->assignment->id, $feedback->userid);

        // store outcomes if needed
        $this->process_outcomes($feedback->userid);

        $submission = $this->get_submission($feedback->userid, true);  // Get or make one

        if (!$grading_info->items[0]->grades[$feedback->userid]->locked and
            !$grading_info->items[0]->grades[$feedback->userid]->overridden) {

            $submission->grade      = $feedback->grade;
            $submission->submissioncomment    = $feedback->submissioncomment;
            $submission->format     = $feedback->format;
            $submission->teacher    = $USER->id;
            $mailinfo = get_user_preferences('assignment_mailinfo', 0);
            if (!$mailinfo) {
                $submission->mailed = 1;       // treat as already mailed
            } else {
                $submission->mailed = 0;       // Make sure mail goes out (again, even)
            }
            $submission->timemarked = time();
			
			//---------------Justin Video Message start 20090105-------------------	
			if(!empty($_POST['mediafilename'])){
				$mediafile = $_POST['mediafilename'];
					  
				if ($mediafile) 
				{						
					//by default we reply in Audio, later we will add a way for this class to distinguish
					//video and audio submissions.
					if (true){
							$submission->poodllfeedback=$mediafile;
					}
				  
				}
			}
			//---------------Video Message end 20090105---------------------

            unset($submission->data1);  // Don't need to update this.
            unset($submission->data2);  // Don't need to update this.

            if (empty($submission->timemodified)) {   // eg for offline assignments
                // $submission->timemodified = time();
            }

            if (! update_record('assignment_submissions', $submission)) {
                return false;
            }

            // triger grade event
            $this->update_grade($submission);

            add_to_log($this->course->id, 'assignment', 'update grades',
                       'submissions.php?id='.$this->assignment->id.'&user='.$feedback->userid, $feedback->userid, $this->cm->id);
        }

        return $submission;

    }

	
	
    function print_student_answer($userid, $return=false){
        global $CFG;
		if (empty($PAGE)) {
			$jsadded="jas not added";
		}else{
			//use this to allow javascript
			$jsadded="jas dded";
			$PAGE->requires->js('mod/poodllonline/poodllonlinejs.js');
		}
		
        if (!$submission = $this->get_submission($userid)) {
            return '';
        }
        		  
		//Output user input Audio and Text, depending on assignment type.
		switch($this->assignment->var3){
			
			case OM_REPLYVOICEONLY:
				if (!empty($submission->data2)){ 
					//$showtext= format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML) . "<BR />";
					$showtext= format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp,embed=true}', FORMAT_HTML);
				}else{
					$showtext= "No Audio Found.";
				}
				break;

			case OM_REPLYVIDEOONLY:
				if (!empty($submission->data2)){ 
					//we show the audio player, because in a list the video player is unwieldly
					//$showtext= format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML) . "<BR />";
					$showtext= format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp,embed=true}', FORMAT_HTML);
				}else{
					$showtext= "No Video Found.";
				}
				break;
			
			case OM_REPLYVOICETHENTEXT:	
				if (!empty($submission->data2)){ 					
					//$showtext= format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML) . "<BR />";
					$showtext= format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp,embed=true}', FORMAT_HTML);
				}else{
					$showtext = "No Audio Found.";					
				}
				break;
			case OM_REPLYVIDEOTHENTEXT:	
				if (!empty($submission->data2)){ 					
					//$showtext= format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML) . "<BR />";
					$showtext= format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp,embed=true}', FORMAT_HTML);
				}else{
					$showtext = "No Video Found.";
				}
				break;
			case OM_REPLYTEXTONLY:
			default:
				   $showtext =shorten_text(trim(strip_tags(format_text($submission->data1,FORMAT_HTML))), 15);
		}				  
				  
				
		$output = '<div class="files">'.
                  '<img src="'.$CFG->pixpath.'/f/html.gif" class="icon" alt="html" />'.
                  link_to_popup_window ('/mod/assignment/type/poodllonline/file.php?id='.$this->cm->id.'&amp;userid='.
                  $submission->userid, 'file'.$userid, $showtext, 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';
				
        return $output;
    }
	
	
	

    function print_user_files($userid, $return=false) {
        global $CFG;

        if (!$submission = $this->get_submission($userid)) {
            return '';
        }


     
		//Output user input Audio and Text, depending on assignment type.
		switch($this->assignment->var3){
			
			case OM_REPLYVOICEONLY:
				if (!empty($submission->data2)){ 
					//print_simple_box(format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML), 'center', '100%');
					print_simple_box(format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML), 'center', '100%');
				}else{
					echo "No Audio Found.";
				}
				break;
				

			case OM_REPLYVIDEOONLY:
				if (!empty($submission->data2)){ 
					//print_simple_box(format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML), 'center', '100%');
					print_simple_box(format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML), 'center', '100%');
				}else{
					echo "No Video Found.";
				}
				break;
			
			case OM_REPLYVOICETHENTEXT:	
				if (!empty($submission->data2)){ 
					//print_simple_box(format_text('{FMS:VOICE='.	$submission->data2.'}', FORMAT_HTML), 'center', '100%');
					print_simple_box(format_text('{POODLL:type=audio,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML), 'center', '100%');
					
					print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
				/// Decide what to count
					if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
						echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, FORMAT_HTML))).')';
					} else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
						echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, FORMAT_HTML))).')';
					}
					print_simple_box_end();
					
					//print text
					print_simple_box(format_text($submission->data1, FORMAT_HTML), 'center', '100%');
					
				}else{
					echo "No Audio Found.";
				}
				break;
			case OM_REPLYVIDEOTHENTEXT:	
				if (!empty($submission->data2)){ 
					//print_simple_box(format_text('{FMS:VIDEO='.	$submission->data2.'}', FORMAT_HTML), 'center', '100%');
					print_simple_box(format_text('{POODLL:type=video,path='.	$submission->data2.',protocol=rtmp}', FORMAT_HTML), 'center', '100%');
					
					print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
				/// Decide what to count
					if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
						echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, FORMAT_HTML))).')';
					} else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
						echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, FORMAT_HTML))).')';
					}
					print_simple_box_end();
					
					//print text
					print_simple_box(format_text($submission->data1, FORMAT_HTML), 'center', '100%');
					
				}else{
					echo "No Video Found.";
				}
				break;
			case OM_REPLYTEXTONLY:
			default:
				   print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
				/// Decide what to count
					if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
						echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, FORMAT_HTML))).')';
					} else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
						echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, FORMAT_HTML))).')';
					}
					print_simple_box_end();
					
					//print text
					print_simple_box(format_text($submission->data1, FORMAT_HTML), 'center', '100%');
					
				
		}
		//end of text and audio output switch
		
    }
	
	/*
	*	Here we print out to pdf
	*	
	*/
	function printToPdf($htmlContent){
	
		$pdf= new pdf;		
		$pdf->print_header = false;
		$pdf->print_footer = false;
		$pdf->AddPage();
		$pdf->writeHTML($htmlcontent, true, 0, true, 0); 
		$pdf->output('document.pdf', 'I');
	}

    function preprocess_submission(&$submission) {
        if ($this->assignment->var1 && empty($submission->submissioncomment)) {  // comment inline
            //We always use html editor Justin 20090225
			//if ($this->usehtmleditor) {
			if (true){
                // Convert to html, clean & copy student data to teacher
                $submission->submissioncomment = format_text($submission->data1, FORMAT_HTML);
                $submission->format = FORMAT_HTML;
            } else {
                // Copy student data to teacher
                $submission->submissioncomment = $submission->data1;
                $submission->format = $submission->data2;
            }
        }
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
		
		
		$mform->addElement('header', 'onlinemediasettings', get_string('onlinemediasettings', 'assignment_poodllonline'));
         // Commented out by TWR since we don't use this anymore
		//media to show / play	within the question
		//$medialist = fetch_medialist($COURSE->id);
		//Just for the talkback players mediapath really.
		//$textattributes = 'size="60"';
        //$mform->addElement('text', 'var2', get_string('poodllresourcename', 'assignment_poodllonline'), $textattributes);
 
		//reply method for student
		$qoptions[OM_REPLYTEXTONLY] = get_string('replytextonly', 'assignment_poodllonline');
		$qoptions[OM_REPLYVOICEONLY] = get_string('replyvoiceonly', 'assignment_poodllonline');
		$qoptions[OM_REPLYVOICETHENTEXT] = get_string('replyvoicethentext', 'assignment_poodllonline');
		$qoptions[OM_REPLYVIDEOONLY] = get_string('replyvideoonly', 'assignment_poodllonline');
		$qoptions[OM_REPLYVIDEOTHENTEXT] = get_string('replyvideothentext', 'assignment_poodllonline');           
		$qoptions[OM_REPLYTALKBACK] = get_string('replytalkback', 'assignment_poodllonline');
        	$mform->addElement('select', 'var3', get_string('replytype', 'assignment_poodllonline'), $qoptions);
		
		//feedback method for teacher
		$qoptions=array();
		$qoptions[OM_FEEDBACKTEXT] = get_string('feedbacktext', 'assignment_poodllonline');
		$qoptions[OM_FEEDBACKTEXTVOICE] = get_string('feedbacktextvoice', 'assignment_poodllonline');
		$qoptions[OM_FEEDBACKTEXTVIDEO] = get_string('feedbacktextvideo', 'assignment_poodllonline');        
        $mform->addElement('select', 'var4', get_string('feedbacktype', 'assignment_poodllonline'), $qoptions);

    }

}

class mod_assignment_poodllonline_edit_form extends moodleform {
    function definition() {
		global $USER;
	
        $mform =& $this->_form;
		
				//Do we need audio or text? or both?
				//the customdata is info we passed in up around line 53 in the view method.
				switch($this->_customdata['assignment']->var3){
					
					case OM_REPLYVOICEONLY:
						//$mediadata= fetchSimpleAudioRecorder('onlinemedia' . $this->_customdata['cm']->id , $USER->id);
						$mediadata= fetchSimpleAudioRecorder('assignment/' . $this->_customdata['assignment']->id , $USER->id);
						//Add the PoodllAudio recorder. Theparams are the def filename and the DOM id of the filename html field to update
						//$mform->addElement('static', 'description', get_string('voicerecorder', 'assignment_poodllonline'),$mediadata);
						$mform->addElement('static', 'description', '',$mediadata);
						//chosho recorder needs to know the id of the checkobox to set it.
						//moodle uses unpredictable ids, so we make our own checkbox when we fetch chosho recorder
						//$mform->addElement('checkbox', 'saveflvvoice', get_string('saverecording', 'assignment_poodllonline'));
						//$mform->addRule('saveflvvoice', get_string('required'), 'required', null, 'client');
						break;

					case OM_REPLYVIDEOONLY:
						//$mediadata= fetchSimpleVideoRecorder('onlinemedia' . $this->_customdata['cm']->id , $USER->id);	
						$mediadata= fetchSimpleVideoRecorder('assignment/' . $this->_customdata['assignment']->id , $USER->id);			
						$mform->addElement('static', 'description', '',$mediadata);						
						//Add the PoodllAudio recorder. Theparams are the def filename and the DOM id of the filename html field to update
						//$mform->addElement('static', 'description', get_string('videorecorder', 'assignment_poodllonline'),$mediadata);
						//recorder needs to know the id of the checkobox to set it.
						//moodle uses unpredictable ids, so we make our own checkbox when we fetch chosho recorder
						//$mform->addElement('checkbox', 'saveflvvoice', get_string('saverecording', 'assignment_poodllonline'));
						//$mform->addRule('saveflvvoice', get_string('required'), 'required', null, 'client');
						break;
					
					case OM_REPLYVOICETHENTEXT:
						//if we have no audio, we force user to make audio before text
						if(empty($this->_customdata['mediapath'])){			
							//Add the PoodllAudio recorder. Theparams are the def filename and the DOM id of the filename html field to update
							//$mediadata= fetchSimpleAudioRecorder('onlinemedia' . $this->_customdata['cm']->id , $USER->id);
							$mediadata= fetchSimpleAudioRecorder('assignment/' . $this->_customdata['assignment']->id , $USER->id);
							//moodle uses unpredictable ids, so we make our own checkbox when we fetch chosho recorder
							//$mform->addElement('checkbox', 'saveflvvoice', get_string('saverecording', 'assignment_poodllonline'));
							//$mform->addRule('saveflvvoice', get_string('required'), 'required', null, 'client');
							$mform->addElement('static', 'description', '',$mediadata);
							//$mform->addElement('static', 'description', get_string('voicerecorder', 'assignment_poodllonline'),$mediadata);
							//we don't give option to write text, so break here
						}else{
						//It should be already displayed
						//at the top of the submission area
						//	$mediadata= format_text('{FMS:VOICE='.	$this->_customdata['mediapath'] .'}', FORMAT_HTML);							
						//	$mform->addElement('static', 'description', get_string('voicerecorder', 'assignment_poodllonline'),$mediadata);
						}
						break;

					case OM_REPLYVIDEOTHENTEXT:
						//if we have no video, we force user to make video before text
						if(empty($this->_customdata['mediapath'])){			
							//Add the Video recorder. Theparams are the def filename and the DOM id of the filename html field to update
							//$mediadata= fetchSimpleVideoRecorder('onlinemedia' . $this->_customdata['cm']->id , $USER->id);
							$mediadata= fetchSimpleVideoRecorder('assignment/' . $this->_customdata['assignment']->id , $USER->id);
							//moodle uses unpredictable ids, so we make our own checkbox when we fetch video recorder
							//$mform->addElement('checkbox', 'saveflvvoice', get_string('saverecording', 'assignment_poodllonline'));
							//$mform->addRule('saveflvvoice', get_string('required'), 'required', null, 'client');
							$mform->addElement('static', 'description', '',$mediadata);
							//$mform->addElement('static', 'description', get_string('videorecorder', 'assignment_poodllonline'),$mediadata);
							//we don't give option to write text, so break here							
						}else{
							//It should be already displayed
							//at the top of the submission area
							//$mediadata= format_text('{FMS:VIDEO='.	$this->_customdata['mediapath'] .'}', FORMAT_HTML);							
							//$mform->addElement('static', 'description', get_string('videorecorder', 'assignment_poodllonline'),$mediadata);
						}
						break;
					
									
				}
				
				//If we are recording text, and we do not need torecord media first
				//We display the text box
				switch ($this->_customdata['assignment']->var3){
						
						case OM_REPLYVIDEOTHENTEXT:
						case OM_REPLYVOICETHENTEXT:
							if (empty($this->_customdata['mediapath'])){
								break;
							}
						case OM_REPLYVOICEONLY:	
						case OM_REPLYVIDEOONLY:
						case OM_REPLYTALKBACK:
							//We do not need a text box, so we just break
							break;
						case OM_REPLYTEXTONLY:							
						default:
								$mediadata="";
								// visible elements
								$mform->addElement('htmleditor', 'text', get_string('submission', 'assignment'), array('cols'=>85, 'rows'=>30));
								$mform->setType('text', PARAM_RAW); // to be cleaned before display
								$mform->setHelpButton('text', array('reading', 'writing', 'richtext'), false, 'editorhelpbutton');
								$mform->addRule('text', get_string('required'), 'required', null, 'client');
								$mform->addElement('format', 'format', get_string('format'));
								$mform->setHelpButton('format', array('textformat', get_string('helpformatting')));							
				}
						
						
						
		
			
		
        // hidden params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // buttons
        $this->add_action_buttons();
    }
}

?>
