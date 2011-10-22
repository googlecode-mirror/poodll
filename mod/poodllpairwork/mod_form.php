<?php //$Id: mod_form.php,v 1.3 2008/08/10 08:05:15 justin Exp $

/**
 * This file defines de main poodllpairwork configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 * 
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             poodllpairwork type (index.php) and in the header 
 *             of the poodllpairwork main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults 
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once ('moodleform_mod.php');
require_once("locallib.php");

class mod_poodllpairwork_mod_form extends moodleform_mod {

	function definition() {

		global $COURSE;
		$mform    =& $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('pairworkname', 'poodllpairwork'), array('size'=>'64'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');
    /// Adding the optional "intro" and "introformat" pair of fields
		$mform->addElement('htmleditor', 'intro', get_string('pairworkintro', 'poodllpairwork'));
		$mform->setType('intro', PARAM_RAW);
		$mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');		
        $mform->addElement('format', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
    /// Adding the rest of poodllpairwork settings, spreeading all them into this fieldset
    /// or adding more fieldsets ('header' elements) if needed for better logic
			//Add the explanations for A and B
    	$mform->addElement('htmleditor', 'introa', get_string('pairwork_a', 'poodllpairwork'));
		$mform->setType('introa', PARAM_RAW);
		$mform->addRule('introa', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('introa', array('writing', 'richtext'), false, 'editorhelpbutton');
		
		$mform->addElement('htmleditor', 'introb', get_string('pairwork_b', 'poodllpairwork'));
		$mform->setType('introb', PARAM_RAW);
		$mform->addRule('introb', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('introb', array('writing', 'richtext'), false, 'editorhelpbutton');



        $mform->addElement('header', 'pairworkfieldset', get_string('pairworkfieldset', 'poodllpairwork'));
        
		$qoptions[SESSIONTYPE_MANUAL] = get_string('userselect', 'poodllpairwork');
		$qoptions[SESSIONTYPE_IP] = get_string('ipselect', 'poodllpairwork');
		$qoptions[SESSIONTYPE_USERNAME] = get_string('sessionselect', 'poodllpairwork');        
        $mform->addElement('select', 'sessiontype', get_string('sessiontype', 'poodllpairwork'), $qoptions);

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
		$this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

	}
}

?>
