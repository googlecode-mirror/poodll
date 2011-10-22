<?php

    /** 
    * controller for summary view
    * 
    * @package mod-poodllflashcard
    * @category mod
    * @author Gustav Delius
    * @contributors Valery Fremaux
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

if ($action == 'reset'){
   $userid = required_param('userid', PARAM_INT);
   delete_records('poodllflashcard_card', 'flashcardid', $flashcard->id, 'userid', $userid);
}
?>
