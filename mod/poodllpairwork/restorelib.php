<?PHP //$Id: restorelib.php,v 1.7 2004/07/01 19:44:55 diml Exp $

     /**
    * This php script contains all the stuff to backup/restore
    * poodllpairwork mods
    *
    * @package mod-poodllpairwork
    * @category mod
    * @author Justin Hunt (poodllsupport@gmail.com)
    * 
    */


    /**
    * restores a complete module
    * @param object $mod
    * @param object $restore
    * @uses $CFG
    */
    function poodllpairwork_restore_mods($mod, $restore) {
        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code, $mod->modtype, $mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the poodllpairwork record structure
            $poodllpairwork->course = $restore->course_id;
            $poodllpairwork->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $poodllpairwork->intro = backup_todb($info['MOD']['#']['INTRO']['0']['#']);
			$poodllpairwork->introa = backup_todb($info['MOD']['#']['INTROA']['0']['#']);
			$poodllpairwork->introb = backup_todb($info['MOD']['#']['INTROB']['0']['#']);
			$poodllpairwork->sessiontype = backup_todb($info['MOD']['#']['SESSIONTYPE']['0']['#']);
            $poodllpairwork->introformat = backup_todb($info['MOD']['#']['INTROFORMAT']['0']['#']);
			$poodllpairwork->timecreated = backup_todb($info['MOD']['#']['TIMECREATED']['0']['#']);
            $poodllpairwork->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);

            //The structure is equal to the db, so insert the poodllpairwork
            $newid = insert_record ('poodllpairwork', $poodllpairwork);

          

            //Do some output     
            echo '<ul><li>' . get_string('modulename', 'poodllpairwork') . " \"" . $poodllpairwork->name . "\"<br>";
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $newid);
                
                //Now restore files
                $status = $status && poodllpairwork_restore_files($mod->id, $newid, $restore);
            } 
            else {
                $status = false;
            }

            //Finalize ul        
            echo '</ul>';

        } else {
            $status = false;
        }

        return $status;
    }

    

    //This function copies the forum related info from backup temp dir to course moddata folder,
    //creating it if needed and recoding everything (forum id and post id)
    function poodllpairwork_restore_files ($oldpairworkid, $newpairworkid, $restore) {
        global $CFG;

        $status = true;
        $todo = false;
        $moddata_path = "";
        $poodllpairwork_path = "";
        $temp_path = "";

        //First, we check to "course_id" exists and create is as necessary
        //in CFG->dataroot
        $dest_dir = $CFG->dataroot."/".$restore->course_id;
        $status = check_dir_exists($dest_dir, true);

        //First, locate course's moddata directory
        $moddata_path = $CFG->dataroot."/".$restore->course_id."/".$CFG->moddata;

        //Check it exists and create it
        $status = check_dir_exists($moddata_path, true);

        //Now, locate forum directory
        if ($status) {
            $poodllpairwork_path = $moddata_path."/poodllpairwork";
            //Check if exists and create it
            $status = check_dir_exists($poodllpairwork_path, true);
        }

        //Now locate the temp dir we are restoring from
        if ($status) {
            $temp_path = $CFG->dataroot."/temp/backup/".$restore->backup_unique_code.
                         "/moddata/poodllpairwork/".$oldpairworkid;
            //Check it exists
            if (is_dir($temp_path)) {
                $todo = true;
            }
        }

        //If todo, we create the neccesary dirs in course moddata/forum
        if ($status and $todo) {
            //First this poodllpairwork id
            $this_poodllpairwork_path = $poodllpairwork_path."/".$newpairworkid;
            $status = check_dir_exists($this_poodllpairwork_path, true);
            //And now, copy temp_path to poodllpairwork_path
            $status = backup_copy_file($temp_path, $this_poodllpairwork_path);
        }

        return $status;
    }

?>
