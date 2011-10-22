<?php  // $Id: questiontype.php,v 1.20.2.8 2008/11/28 06:07:24 tjhunt Exp $

//////////////////
///   poodllrecording   ///
/////////////////

//Get our poodll resource handling lib
require_once($CFG->libdir . '/poodllresourcelib.php');

/// QUESTION TYPE CLASS //////////////////
/**
 * @package questionbank
 * @subpackage questiontypes
 */
class question_poodllrecording_qtype extends default_questiontype {
    var $usablebyrandom;

    function question_poodllrecording_qtype() {
        $this->usablebyrandom = get_config('qtype_random', 'selectmanual');
    }

    function name() {
        return 'poodllrecording';
    }

    function is_manual_graded() {
        return true;
    }

    function is_usable_by_random() {
        return $this->usablebyrandom;
    }

    function save_question_options($question) {
        $result = true;
        $update = true;
        $answer = get_record("question_answers", "question", $question->id);
        if (!$answer) {
            $answer = new stdClass;
            $answer->question = $question->id;
            $update = false;
        }
        $answer->answer   = $question->feedback;
        $answer->feedback = $question->feedback;
        $answer->fraction = $question->fraction;
        if ($update) {
            if (!update_record("question_answers", $answer)) {
                $result = new stdClass;
                $result->error = "Could not update quiz answer!";
            }
        } else {
            if (!$answer->id = insert_record("question_answers", $answer)) {
                $result = new stdClass;
                $result->error = "Could not insert quiz answer!";
            }
        }
        return $result;
    }

    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        global $CFG, $USER;
        static $htmleditorused = false;

        $answers       = &$question->options->answers;
        $readonly      = empty($options->readonly) ? '' : 'disabled="disabled"';

        // *RELIC of essay question type* Only use the rich text editor for the first poodllrecording question on a page.
      //  $usehtmleditor = can_use_html_editor() && !$htmleditorused;

        $formatoptions          = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->para    = false;

        $inputname = $question->name_prefix;
        $stranswer = get_string("answer", "quiz").': ';

        /// set question text and media
        $questiontext = format_text($question->questiontext,
                                   $question->questiontextformat,
                                   $formatoptions, $cmoptions->course);

        $image = get_question_image($question);

        // feedback handling
        $feedback = '';
        if ($options->feedback && !empty($answers)) {
            foreach ($answers as $answer) {
                $feedback = format_text($answer->feedback, '', $formatoptions, $cmoptions->course);
            }
        }

        // get response value
        if (isset($state->responses[''])) {
			//relic of essay question type
            //$value = stripslashes_safe($state->responses['']);
			$value = $state->responses[''];
        } else {
            $value = "";
        }

        // answer
        if (empty($options->readonly)) {
            // *RELIC of essay question type* the student needs to record their voice  or video so lets give them their recorder.
           // $answer = print_textarea($usehtmleditor, 18, 80, 630, 400, $inputname, $value, $cmoptions->course, true);

		   $answer = fetchSimpleAudioRecorder('question/' . $question->id  ,$USER->id, $inputname,'') .
					 '<input type="hidden" value="" id="' . $inputname . '" name="' . $inputname . '" />';		 
        } else {
            // it is read only, so just format the students answer and output it
			
			// *RELIC of essay question type*
		/*
            $safeformatoptions = new stdClass;
            $safeformatoptions->para = false;
            $answer = format_text($value, FORMAT_MOODLE,
                                  $safeformatoptions, $cmoptions->course);
			$answer = '<div class="answerreview">' . $answer . '</div>';					  
          */
			//$answer = $value ;	

				//this will show an audio player both to the grading teacher and to the student reviewing
			$answer = fetchSimpleAudioPlayer($value, 'rtmp', 250,30,false);
			
        }

        include("$CFG->dirroot/question/type/poodllrecording/display.html");

		// *RELIC of essay question type*
		/*
        if ($usehtmleditor && empty($options->readonly)) {
            use_html_editor($inputname);
            $htmleditorused = true;
        }
		*/
    }

    function grade_responses(&$question, &$state, $cmoptions) {
        // All grading takes place in Manual Grading
		//relic of old qEssay question
        $state->responses[''] = clean_param($state->responses[''], PARAM_CLEAN);
		
		//Here is the place to massage the answer before it goes in the DB, but for audio it was not necessary
		//tried these anyway but none got filtered or into the db without sql errors
		//$state->responses[''] = "{POODLL:type=audio,path=" . $state->responses['']. ",protocol=rtmp} ";
		//$state->responses[''] = clean_param(fetchSimpleAudioPlayer($state->responses[''], 'rtmp', 250,30,true), PARAM_CLEAN);

        $state->raw_grade = 0;
        $state->penalty = 0;

        return true;
    }

    function response_summary($question, $state, $length = 80) {
        $responses = $this->get_actual_response($question, $state);
        $response = reset($responses);
		//relic of old Essay question
       // return shorten_text($response, $length);
	   
	   //it would be nice to drop in a player or a link to a player (embedded =true)
	   //but for now lets just put the text "audio file"
	   //i cant even find where it is used.
	   return 'audio file';
	   //return fetchSimpleAudioPlayer($response, 'rtmp', 250,30,false);
    }

    /**
     * Backup the extra information specific to an poodllrecording question - over and above
     * what is in the mdl_question table.
     *
     * @param file $bf The backup file to write to.
     * @param object $preferences the blackup options controlling this backup.
     * @param $questionid the id of the question being backed up.
     * @param $level indent level in the backup file - so it can be formatted nicely.
     */
    function backup($bf, $preferences, $questionid, $level = 6) {
        return question_backup_answers($bf, $preferences, $questionid, $level);
    }

    /**
     * Runs all the code required to set up and save an poodllrecording question for testing purposes.
     * Alternate DB table prefix may be used to facilitate data deletion.
     */
    function generate_test($name, $courseid = null) {
        list($form, $question) = parent::generate_test($name, $courseid);
        $form->questiontext = "What is the purpose of life?";
        $form->feedback = "feedback";
        $form->generalfeedback = "General feedback";
        $form->fraction = 0;
        $form->penalty = 0;

        if ($courseid) {
            $course = get_record('course', 'id', $courseid);
        }

        return $this->save_question($question, $form, $course);
    }

    // Restore method not needed.
}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
question_register_questiontype(new question_poodllrecording_qtype());
?>
