<?php

    /** 
    * This view provides a summary for the teacher
    * 
    * @package mod-poodllflashcard
    * @category mod
    * @author Valery Fremaux, Gustav Delius
    * @contributors
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

    // security
    if (!defined('MOODLE_INTERNAL')){
        error("Illegal direct access to this screen");
    }

    /**
    if ($action != ''){
        include "{$CFG->dirroot}/mod/poodllflashcard/cardsummaryview.controller.php";
    }
    */

    $cards = flashcard_get_card_status($flashcard);
        
    $strcard = get_string('card', 'poodllflashcard');
    $strviewed = get_string('viewed', 'poodllflashcard');
    $strdecks = get_string('decks', 'poodllflashcard');

    $table->head = array("<b>$strcard</b>", "<b>$strdecks</b>", "<b>$strviewed</b>");
    $table->size = array('30%', '35%', '35%');
    $table->width = "90%";
    
    foreach($cards as $cardquestion => $acard){
        $cardcounters = flashcard_print_cardcounts($flashcard, $acard, true);
        $table->data[] = array($cardquestion, $cardcounters, $acard->accesscount);
    }    
    
    print_table($table);
?>
