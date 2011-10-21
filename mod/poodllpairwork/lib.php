<?php  // $Id: lib.php,v 1.8 2007/12/12 00:09:46 stronk7 Exp $
/**
 * Library of functions and constants for module poodllpairwork
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the poodllpairwork specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

/// (replace poodllpairwork with the name of your module and delete this line)

$poodllpairwork_CONSTANT = 7;     /// for example

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted pairwork record
 **/
function poodllpairwork_add_instance($pairwork) {
    
    // temp added for debugging
    echo "ADD INSTANCE CALLED";
   // print_object($pairwork);
    
    $pairwork->timecreated = time();

    # May have to add extra stuff in here #
    
    return insert_record("poodllpairwork", $pairwork);
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function poodllpairwork_update_instance($pairwork) {

    $pairwork->timemodified = time();
    $pairwork->id = $pairwork->instance;

    # May have to add extra stuff in here #

    return update_record("poodllpairwork", $pairwork);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function poodllpairwork_delete_instance($id) {

    if (! $pairwork = get_record("poodllpairwork", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("poodllpairwork", "id", "$pairwork->id")) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function poodllpairwork_user_outline($course, $user, $mod, $pairwork) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function poodllpairwork_user_complete($course, $user, $mod, $pairwork) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in poodllpairwork activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function poodllpairwork_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function poodllpairwork_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $pairworkid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function poodllpairwork_grades($pairworkid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of poodllpairwork. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $pairworkid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function poodllpairwork_get_participants($pairworkid) {
    return false;
}

/**
 * This function returns if a scale is being used by one pairwork
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $pairworkid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function poodllpairwork_scale_used ($pairworkid,$scaleid) {
    $return = false;

    //$rec = get_record("poodllpairwork","id","$pairworkid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of poodllpairwork.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any poodllpairwork
 */
function poodllpairwork_scale_used_anywhere($scaleid) {
    if ($scaleid and record_exists('poodllpairwork', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function poodllpairwork_install() {
     return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function poodllpairwork_uninstall() {
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other pairwork functions go here.  Each of them must have a name that 
/// starts with pairwork_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.


?>
