<?php

/** 
* This view allows checking deck states
* 
* @package mod-poodllflashcard
* @category mod
* @author Gustav Delius
* @contributors Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

/**
* Requires and includes 
*/
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->libdir.'/questionlib.php');
require_once ($CFG->dirroot.'/mod/poodllflashcard/locallib.php');

/**
* overrides moodleform for poodllflashcard setup
*/
class mod_poodllflashcard_mod_form extends moodleform_mod {

	function definition() {

		global $CFG, $COURSE;
		$mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');

		$mform->addElement('htmleditor', 'summary', get_string('summary', 'poodllflashcard'));
		$mform->setType('summary', PARAM_RAW);
        $mform->setHelpButton('summary', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');
		// $mform->addRule('summary', get_string('required'), 'required', null, 'client');

        $startdatearray[] = &$mform->createElement('date_time_selector', 'starttime', '');
        $startdatearray[] = &$mform->createElement('checkbox', 'starttimeenable', '');
        $mform->addGroup($startdatearray, 'startfrom', get_string('starttime', 'poodllflashcard'), ' ', false);
        $mform->disabledIf('startfrom', 'starttimeenable');

        $enddatearray[] = &$mform->createElement('date_time_selector', 'endtime', '');
        $enddatearray[] = &$mform->createElement('checkbox', 'endtimeenable', '');
        $mform->addGroup($enddatearray, 'endfrom', get_string('endtime', 'poodllflashcard'), ' ', false);
        $mform->disabledIf('endfrom', 'endtimeenable');

        if (!$questions = get_records_select('question', "qtype='match'", '', 'id, name')) {
            $questions = array();
        } else {
            // prepared for 1.9 questionbanck compatibility
            if (function_exists('question_has_capability_on')){

                function drop_questions($a){                    
                    return question_has_capability_on($a->id, 'use');
                } 

                $questions = array_filter($questions, 'drop_questions');
            } 
        }
        foreach($questions as $question){
            $qoptions[$question->id] = $question->name;
        }
        $mform->addElement('select', 'questionid', get_string('questionid', 'poodllflashcard'), $qoptions);
        $mform->setHelpButton('questionid', array('sourcequestion', get_string('questionid', 'poodllflashcard'), 'poodllflashcard'));

        $mform->addElement('checkbox', 'forcereload', get_string('forcereload', 'poodllflashcard'));
        $mform->setHelpButton('forcereload', array('forcereload', get_string('forcereload', 'poodllflashcard'), 'poodllflashcard'));

        $stylingtext = get_string('customisation', 'poodllflashcard', $CFG->wwwroot."/files/index.php?id={$COURSE->id}&amp;wdir=%2Fmoddata%2Fpoodllflashcard");
        $stylingtext .= "<br/><br/><center><a href=\"$CFG->wwwroot/mod/poodllflashcard/poodllflashcard.css\" target=\"_blank\">".get_string('stylesheet', 'poodllflashcard')."</a></center>";
        $mform->addElement('static', 'style', get_string('styling', 'poodllflashcard'), $stylingtext);

        $mediaoptions[FLASHCARD_MEDIA_TEXT] = get_string('text', 'poodllflashcard');
        $mediaoptions[FLASHCARD_MEDIA_IMAGE] = get_string('image', 'poodllflashcard');
        $mediaoptions[FLASHCARD_MEDIA_SOUND] = get_string('sound', 'poodllflashcard');
        $mediaoptions[FLASHCARD_MEDIA_IMAGE_AND_SOUND] = get_string('imageplussound', 'poodllflashcard');
        $mform->addElement('select', 'questionsmediatype', get_string('questionsmediatype', 'poodllflashcard'), $mediaoptions);
        $mform->setHelpButton('questionsmediatype', array('mediatypes', get_string('questionsmediatype', 'poodllflashcard'), 'poodllflashcard'));

        $mform->addElement('select', 'answersmediatype', get_string('answersmediatype', 'poodllflashcard'), $mediaoptions);
        $mform->setHelpButton('answersmediatype', array('mediatypes', get_string('answersmediatype', 'poodllflashcard'), 'poodllflashcard'));

        $mform->addElement('selectyesno', 'flipdeck', get_string('flipdeck', 'poodllflashcard'));
        $mform->setHelpButton('flipdeck', array('flipdeck', get_string('flipdeck', 'poodllflashcard'), 'poodllflashcard'));

        $options['2'] = 2;
        $options['3'] = 3;
        $options['4'] = 4;
        $mform->addElement('select', 'decks', get_string('decks', 'poodllflashcard'), $options);
        $mform->setType('decks', PARAM_INT); 
        $mform->setDefault('decks', 2); 
        $mform->setHelpButton('decks', array('decks', get_string('decks', 'poodllflashcard'), 'poodllflashcard'));

        $mform->addElement('selectyesno', 'autodowngrade', get_string('autodowngrade', 'poodllflashcard'));
        $mform->setHelpButton('autodowngrade', array('autodowngrade', get_string('autodowngrade', 'poodllflashcard'), 'poodllflashcard'));

        $mform->addElement('text', 'deck2_release', get_string('deck2_release', 'poodllflashcard'), array('size'=>'5'));
        $mform->setHelpButton('deck2_release', array('deck_release', get_string('deck2_release', 'poodllflashcard'), 'poodllflashcard'));
        $mform->setType('deck2_release', PARAM_INT);
        $mform->setDefault('deck2_release', 96);
        $mform->addRule('deck2_release', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');
 
        $mform->addElement('text', 'deck3_release', get_string('deck3_release', 'poodllflashcard'), array('size'=>'5'));
        $mform->setType('deck3_release', PARAM_INT);
        $mform->setDefault('deck3_release', 96);
        $mform->addRule('deck3_release', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_release', 'decks', 'eq', 2);

        $mform->addElement('text', 'deck4_release', get_string('deck4_release', 'poodllflashcard'), array('size'=>'5'));
        $mform->setType('deck4_release', PARAM_INT);
        $mform->setDefault('deck4_release', 96);
        $mform->addRule('deck4_release', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_release', 'decks', 'neq', 4);

        $mform->addElement('text', 'deck1_delay', get_string('deck1_delay', 'poodllflashcard'), array('size'=>'5'));
        $mform->setHelpButton('deck1_delay', array('deck_delay', get_string('deck1_delay', 'poodllflashcard'), 'poodllflashcard'));
        $mform->setType('deck1_delay', PARAM_INT);
        $mform->setDefault('deck1_delay', 48);
        $mform->addRule('deck1_delay', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck2_delay', get_string('deck2_delay', 'poodllflashcard'), array('size'=>'5'));
        $mform->setType('deck2_delay', PARAM_INT);
        $mform->setDefault('deck2_delay', 96);
        $mform->addRule('deck2_delay', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck3_delay', get_string('deck3_delay', 'poodllflashcard'), array('size'=>'5'));
        $mform->setType('deck3_delay', PARAM_INT);
        $mform->setDefault('deck3_delay', 168);
        $mform->addRule('deck3_delay', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_delay', 'decks', 'eq', 2);

        $mform->addElement('text', 'deck4_delay', get_string('deck4_delay', 'poodllflashcard'), array('size'=>'5'));
        $mform->setType('deck4_delay', PARAM_INT);
        $mform->setDefault('deck4_delay', 336);
        $mform->addRule('deck4_delay', get_string('numericrequired', 'poodllflashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_delay', 'decks', 'neq', 4);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
	}

    /**	
	function definition_after_data(){
		$mform    =& $this->_form;
        $startfrom =&$mform->getElement('startfrom');
        $elements = $startfrom->getElements();
        print_object($elements[1]->getValue());
        if ($mform->getElementValue('starttime') != 0){
            $starttimeenable->setValue(true);
        }
	}*/
	
	function validation($data) {
	    $errors = array();

        if ($data['starttime'] > $data['endtime']){
            $errors['endfrom'] = get_string('mustbehigherthanstart', 'poodllflashcard');
        }
	    
	    if ($data['decks'] >= 2){
	        if ($data['deck1_delay'] > $data['deck2_delay']) {
	            $errors['deck2_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 3){
	        if ($data['deck2_delay'] > $data['deck3_delay']) {
	            $errors['deck3_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 4){
	        if ($data['deck3_delay'] > $data['deck4_delay']) {
	            $errors['deck4_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    return $errors;
	}

}
?>
