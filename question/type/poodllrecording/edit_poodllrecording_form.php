<?php  // $Id: edit_poodllrecording_form.php,v 1.7.2.2 2009/02/19 01:09:36 tjhunt Exp $
/**
 * Defines the editing form for the poodllrecording question type.
 *
 * @copyright &copy; 2007 Jamie Pratt
 * @author Jamie Pratt me@jamiep.org
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage questiontypes
 */

/**
 * poodllrecording editing form definition.
 */
class question_edit_poodllrecording_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    function definition_inner(&$mform) {
        $mform->addElement('htmleditor', 'feedback', get_string("feedback", "quiz"),
                                array('course' => $this->coursefilesid));
        $mform->setType('feedback', PARAM_RAW);

        $mform->addElement('hidden', 'fraction', 0);

        //don't need this default element.
        $mform->removeElement('penalty');
        $mform->addElement('hidden', 'penalty', 0);
    }

    function set_data($question) {
        if (!empty($question->options) && !empty($question->options->answers)) {      	
            $answer = reset($question->options->answers);
            $question->feedback = $answer->feedback;
        }
        $question->penalty = 0;
        parent::set_data($question);
    }

    function qtype() {
        return 'poodllrecording';
    }
}
?>
