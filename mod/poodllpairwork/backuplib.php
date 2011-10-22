<?PHP //$Id: backuplib.php,v 1.3 2004/07/01 19:44:55 diml Exp $

    /**
    * This php script contains all the stuff to backup/restore
    * poodllpairwork mods
    *
    * @package mod-poodllpairwork
    * @category mod
    * @author Justin Hunt (poodllsupport@gmail.com)
    * 
    */


    function poodllpairwork_backup_mods($bf, $preferences) {
        global $CFG;

        $status = true;

        //Iterate over poodllpairwork table
        $poodllpairworks = get_records('poodllpairwork', 'course', $preferences->backup_course, 'id');
        if ($poodllpairworks) {
            foreach ($poodllpairworks as $poodllpairwork) {
                $status = $status && poodllpairwork_backup_one_mod($bf, $preferences, $poodllpairwork);
            }
        }
        return $status;
    }

    function poodllpairwork_backup_one_mod($bf, $preferences, $poodllpairwork) {
        global $CFG;
        
        if (is_numeric($poodllpairwork)) {
            $poodllpairwork = get_record('poodllpairwork', 'id', $poodllpairwork);
        }

        $status = true;

        fwrite ($bf, start_tag('MOD', 3, true));
        //Print choice data
		fwrite ($bf,full_tag('MODTYPE', 4, false, 'poodllpairwork'));
        fwrite ($bf,full_tag('ID', 4, false, $poodllpairwork->id));
        fwrite ($bf,full_tag('COURSE', 4, false, 'poodllpairwork'));
        fwrite ($bf,full_tag('NAME', 4, false, $poodllpairwork->name));
        fwrite ($bf,full_tag('INTRO', 4, false, $poodllpairwork->intro));
        fwrite ($bf,full_tag('INTROA', 4, false, $poodllpairwork->introa));
		fwrite ($bf,full_tag('INTROB', 4, false, $poodllpairwork->introb));
		fwrite ($bf,full_tag('SESSIONTYPE', 4, false, $poodllpairwork->sessiontype));
		fwrite ($bf,full_tag('INTROFORMAT', 4, false, $poodllpairwork->introformat));
		fwrite ($bf,full_tag('TIMECREATED', 4, false, $poodllpairwork->timecreated));
        fwrite ($bf,full_tag('TIMEMODIFIED', 4, false, $poodllpairwork->timemodified));

       
        /// End mod
        $status = $status && fwrite ($bf, end_tag('MOD', 3, true));
        return $status;
    }


  
    /// Backup poodllpairwork files for images or sounds
    function backup_poodllpairwork_files_instance($bf, $preferences, $instanceid) {
        global $CFG;

        $status = true;

        //First we check to moddata exists and create it as necessary
        //in temp/backup/$backup_code  dir
        $status = check_and_create_moddata_dir($preferences->backup_unique_code);
        $status = check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/poodllpairwork/",true);
        //Now copy the poodllpairwork dir
        if ($status) {
            //Only if it exists !! Thanks to Daniel Miksik.
            if (is_dir($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/poodllpairwork/".$instanceid)) {
                $status = backup_copy_file($CFG->dataroot."/".$preferences->backup_course."/".$CFG->moddata."/poodllpairwork/".$instanceid,
                                           $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code."/moddata/poodllpairwork/".$instanceid);
            }
        }

        return $status;

    }

   /// Return an array of info (name,value)
   function poodllpairwork_check_backup_mods($course, $user_data = false, $backup_unique_code) {

        // First the course data
        $info[0][0] = get_string('modulenameplural', 'poodllpairwork');
        if ($ids = poodllpairwork_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        return $info;
    }

    // Returns an array of poodllpairwork id
    function poodllpairwork_ids ($course) {
        global $CFG;

        $query = "
            SELECT 
                f.id, 
                f.course
            FROM 
                {$CFG->prefix}poodllpairwork f
            WHERE 
                f.course = '{$course}'
        ";
        return get_records_sql ($query);
    }

 

?>
