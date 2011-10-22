<?PHP //$Id: backuplib.php,v 1.3 2004/07/01 19:44:55 diml Exp $

    /**
    * This php script contains all the stuff to backup/restore
    * poodllflashcard mods
    *
    * @package mod-flashcard
    * @category mod
    * @author Valery Fremaux (admin@ethnoinformatique.fr)
    * @modified for PoodLL justin thanks Valery Fremaux for her incredible mod
    * 
    */

    //This is the "graphical" structure of the poodllflashcard mod:
    //
    //           poodllflashcard                                  
    //          (CL,pk->id)               
    //               |                       
    //               +-------------------------------------------+
    //               |                                           |
    //         poodllflashcard_card                            poodllflashcard_deckdata 
    //  (UL,pk->id, fk->flashcardid, fk->entryid)  (IL, pk->id, fk->flashcardid,files)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          IL->instance level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    function poodllflashcard_backup_mods($bf, $preferences) {
        global $CFG;

        $status = true;

        //Iterate over poodllflashcard table
        $flashcards = get_records('poodllflashcard', 'course', $preferences->backup_course, 'id');
        if ($flashcards) {
            foreach ($flashcards as $flashcard) {
                $status = $status && poodllflashcard_backup_one_mod($bf, $preferences, $flashcard);
            }
        }
        return $status;
    }

    function poodllflashcard_backup_one_mod($bf, $preferences, $flashcard) {
        global $CFG;
        
        if (is_numeric($flashcard)) {
            $flashcard = get_record('poodllflashcard', 'id', $flashcard);
        }

        $status = true;

        fwrite ($bf, start_tag('MOD', 3, true));
        //Print choice data
        fwrite ($bf,full_tag('ID', 4, false, $flashcard->id));
        fwrite ($bf,full_tag('MODTYPE', 4, false, 'poodllflashcard'));
        fwrite ($bf,full_tag('NAME', 4, false, $flashcard->name));
        fwrite ($bf,full_tag('SUMMARY', 4, false, $flashcard->summary));
        fwrite ($bf,full_tag('SUMMARYFORMAT', 4, false, $flashcard->summaryformat));
        fwrite ($bf,full_tag('TIMEMODIFIED', 4, false, $flashcard->timemodified));
        fwrite ($bf,full_tag('STARTTIME', 4, false, $flashcard->starttime));
        fwrite ($bf,full_tag('ENDTIME', 4, false, $flashcard->endtime));
        fwrite ($bf,full_tag('QUESTIONID', 4, false, $flashcard->questionid));
        fwrite ($bf,full_tag('AUTODOWNGRADE', 4, false, $flashcard->autodowngrade));
        fwrite ($bf,full_tag('DECKS', 4, false, $flashcard->decks));
        fwrite ($bf,full_tag('DECK2_RELEASE', 4, false, $flashcard->deck2_release));
        fwrite ($bf,full_tag('DECK3_RELEASE', 4, false, $flashcard->deck3_release));  
        fwrite ($bf,full_tag('DECK4_RELEASE', 4, false, $flashcard->deck4_release));
        fwrite ($bf,full_tag('DECK1_DELAY', 4, false, $flashcard->deck1_delay));
        fwrite ($bf,full_tag('DECK2_DELAY', 4, false, $flashcard->deck2_delay));
        fwrite ($bf,full_tag('DECK3_DELAY', 4, false, $flashcard->deck3_delay));
        fwrite ($bf,full_tag('DECK4_DELAY', 4, false, $flashcard->deck4_delay));
        fwrite ($bf,full_tag('QUESTIONSMEDIATYPE', 4, false, $flashcard->questionsmediatype));
        fwrite ($bf,full_tag('ANSWERSMEDIATYPE', 4, false, $flashcard->answersmediatype));
        fwrite ($bf,full_tag('FLIPDECK', 4, false, $flashcard->flipdeck));

        $status = $status && backup_flashcard_deck($bf, $preferences, $flashcard);
        $status = $status && backup_flashcard_files_instance($bf, $preferences, $flashcard->id);

        if ($preferences->mods['poodllflashcard']->userinfo) {
            $status = $status && backup_flashcard_cards($bf, $preferences, $flashcard);
        }

        /// End mod
        $status = $status && fwrite ($bf, end_tag('MOD', 3, true));
        return $status;
    }

    /**
    * Backup flashcard deck constitution (executed from flashcard_backup_mods)
    */
    function backup_flashcard_deck($bf, $preferences, &$flashcard) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('DECK', 4, true));

        $cards = get_records('poodllflashcard_deckdata', 'flashcardid', $flashcard->id);
        /// If there is card
        if ($cards) {
            /// Iterate over each card of the deck
            foreach ($cards as $card) {
                /// Start card
                $status = $status && fwrite ($bf, start_tag('CARD', 5, true));
                /// Print card data
                fwrite ($bf, full_tag('ID', 6, false, $card->id));
                fwrite ($bf, full_tag('FLASHCARDID', 6, false, $card->flashcardid));
                fwrite ($bf, full_tag('QUESTIONTEXT', 6, false, $card->questiontext));
                fwrite ($bf, full_tag('ANSWERTEXT', 6, false, $card->answertext));
                /// End card
                $status = $status && fwrite ($bf, end_tag('CARD', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('DECK', 4, true));
        return $status;
    }

    /**
    * Backup flashcard_cards (executed from flashcard_backup_mods)
    */
    function backup_flashcard_cards($bf, $preferences, &$flashcard) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('CARDS', 4, true));

        $cards = get_records('poodllflashcard_card', 'flashcardid', $flashcard->id);
        /// If there is card
        if ($cards) {
            /// Iterate over each card
            foreach ($cards as $card) {
                /// Start card
                $status = $status && fwrite ($bf, start_tag('CARD', 5, true));
                /// Print card data
                fwrite ($bf, full_tag('ID', 6, false, $card->id));
                fwrite ($bf, full_tag('FLASHCARDID', 6, false, $card->flashcardid));
                fwrite ($bf, full_tag('USERID', 6, false, $card->userid));
                fwrite ($bf, full_tag('ENTRYID', 6, false, $card->entryid));
                fwrite ($bf, full_tag('DECK', 6, false, $card->deck));
                fwrite ($bf, full_tag('LASTACCESSED', 6, false, $card->lastaccessed));
                fwrite ($bf, full_tag('ACCESSCOUNT', 6, false, $card->accesscount));
                /// End card
                $status = $status && fwrite ($bf, end_tag('CARD', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('CARDS', 4, true));
        return $status;
    }

    /// Backup flashcard files for images or sounds
    function backup_flashcard_files_instance($bf, $preferences, $instanceid) {
        global $CFG;

        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/poodllflashcard/",true);
        //Now copy the flashcard dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/poodllflashcard/".$instanceid)) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/poodllflashcard/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/poodllflashcard/".$instanceid);
            }
        }

        return $status;

    }

   /// Return an array of info (name,value)
   function poodllflashcard_check_backup_mods($course, $user_data = false, $backup_unique_code) {

        // First the course data
        $info[0][0] = get_string('modulenameplural', 'poodllflashcard');
        if ($ids = flashcard_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        $info[1][0] = get_string('deck', 'poodllflashcard');
        if ($ids = flashcard_deckdata_ids($course)) {
            $info[1][1] = count($ids);
        } else {
            $info[1][1] = 0;
        }

        if ($user_data){
            $info[2][0] = get_string('userdecks', 'poodllflashcard');
            if ($ids = flashcard_cards_ids($course)) {
                $info[2][1] = count($ids);
            } else {
                $info[2][1] = 0;
            }
        }

        return $info;
    }

    // Returns an array of flashcard id
    function flashcard_ids ($course) {
        global $CFG;

        $query = "
            SELECT 
                f.id, 
                f.course
            FROM 
                {$CFG->prefix}poodllflashcard f
            WHERE 
                f.course = '{$course}'
        ";
        return get_records_sql ($query);
    }

    // Returns an array of flashcard card id in deck
    function flashcard_deckdata_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                dd.id, 
                f.course
            FROM 
                {$CFG->prefix}poodllflashcard f,
                {$CFG->prefix}poodllflashcard_deckdata dd
            WHERE
                f.id = dd.flashcardid AND 
                f.course = '{$course}'
        ";
        return get_records_sql($query);
    }

    // Returns an array of flashcard card id
    function flashcard_cards_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                c.id, 
                f.course
            FROM 
                {$CFG->prefix}poodllflashcard f,
                {$CFG->prefix}poodllflashcard_card c
            WHERE
                f.id = c.flashcardid AND 
                f.course = '{$course}'
        ";
        return get_records_sql($query);
    }

?>
