<?php
/** 
* a controller for the play view
* 
* @package mod-poodllflashcard
* @category mod
* @author Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*
* @usecase add
* @usecase delete
* @usecase save
*/

/******************************** Add new blank fields *****************************/
if ($action == 'add'){
    $add = required_param('add', PARAM_INT);
    $card->flashcardid = $flashcard->id;
    $users = get_records_menu('poodllflashcard_card', 'flashcardid', $flashcard->id, '', 'DISTINCT userid, id');
    for($i = 0 ; $i < $add ; $i++){
        if (!$newcardid = insert_record('poodllflashcard_deckdata', $card)){
            error ("Could not add card to deck");
        }
        if ($users){
            foreach(array_keys($users) as $userid){
                $deckcard->flashcardid = $flashcard->id;
                $deckcard->entryid = $newcardid;
                $deckcard->userid = $userid;
                $deckcard->lastaccessed = 0;
                $deckcard->deck = 1;
                $deckcard->accesscount = 0;
                if (!insert_record('poodllflashcard_card', $deckcard)){
                    error ("Could not bind card to user $userid deck");
                }
            }
        }
    }
}
/******************************** Delete a set of records *****************************/
if ($action == 'delete'){
    $items = required_param('items', PARAM_INT);
    if (is_array($items)) $items = implode(',', $items);
    $items = str_replace(",", "','", $items);

    if (!delete_records_select('poodllflashcard_deckdata', " id IN ('$items') ")){
        error ("Could not add card to deck");
    }

    if (!delete_records_select('poodllflashcard_card', " entryid IN ('$items') ")){
        error ("Could not add card to deck");
    }
}
/******************************** Save and update all questions *****************************/
if ($action == 'save'){
	$keys = array_keys($_POST);				// get the key value of all the fields submitted
	$qkeys = preg_grep('/^q/' , $keys);  	// filter out only the status
	$akeys = preg_grep('/^a/' , $keys);  	// filter out only the assigned updating

    foreach($qkeys as $akey){
        preg_match("/[qi](\d+)/", $akey, $matches);
        $card->id = $matches[1];
        $card->flashcardid = $flashcard->id;
        if ($flashcard->questionsmediatype != FLASHCARD_MEDIA_IMAGE_AND_SOUND){
            $card->questiontext = required_param("q{$card->id}", PARAM_TEXT);
        } else {
            // combine image and sound in one single field
            $card->questiontext = required_param("i{$card->id}", PARAM_TEXT).'@'.required_param("s{$card->id}", PARAM_TEXT);
        }
        if ($flashcard->answersmediatype != FLASHCARD_MEDIA_IMAGE_AND_SOUND){
            $card->answertext = required_param("a{$card->id}", PARAM_TEXT);
        } else {
            // combine image and sound in one single field
            $card->answertext = required_param("i{$card->id}", PARAM_TEXT).'@'.required_param("s{$card->id}", PARAM_TEXT);
        }
        if (!update_record('poodllflashcard_deckdata', $card)){
            error("Could not update deck card");
        }
    }
}
?>
