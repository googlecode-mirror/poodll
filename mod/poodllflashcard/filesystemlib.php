<?php
/**
 * @author Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle 
 *
 * File System Abstract Layer
 * Support library
 *
 * 2007-11-04  File created.
 */

/**
Function Set:

function filesystem_create_dir($path, $recursive = 0) {
function filesystem_is_dir($relativepath){
function filesystem_file_exists($relativepath){
function filesystem_scan_dir($relativepath, $hiddens = 0, $what = 0){
function filesystem_clear_dir($relativepath, $fullDelete = false) {
function filesystem_copy_tree($source, $dest) {
function filesystem_store_file($path, $data) {
function filesystem_read_a_file($file) {
function filesystem_delete_file($file){
function filesystem_remove_dir($relativepath){
function filesystem_move_file($source,$dest){
function filesystem_copy_file($source, $dest) {
function filesystem_get_file_list($path, $filemask = "*.*") {

// function getFileIcon($mimetype){
// function getMimeType($extension){

*/

define('FS_RECURSIVE', true);
define('FS_NON_RECURSIVE', false);

define('FS_SHOW_HIDDEN', true);
define('FS_IGNORE_HIDDEN', false);

define('FS_NO_DIRS', 2);
define('FS_ONLY_DIRS', 1);
define('FS_ALL_ENTRIES', 0);

define('FS_FULL_DELETE', true);
define('FS_CLEAR_CONTENT', false);

/**
$_MIMES = array();
$_MIMES['TXT'] = "text/raw";
$_MIMES['HTM'] = "text/html";
$_MIMES['HTML'] = "text/html";
$_MIMES['DIM'] = "application/x-essi-parsed";
$_MIMES['DIML'] = "application/x-essi-parsed";
$_MIMES['JS'] = "text/javascript";
$_MIMES['DOC'] = "application/x-word";
$_MIMES['XLS'] = "application/x-excel";
$_MIMES['XML'] = "text/xml";
$_MIMES['XSL'] = "text/xsl";
$_MIMES['PPT'] = "application/x-powerpoint";
$_MIMES['BMP'] = "image/bmp";
$_MIMES['GIF'] = "image/gif";
$_MIMES['JPG'] = "image/jpg";
$_MIMES['JPEG'] = "image/jpg";
$_MIMES['PNG'] = "image/png";
$_MIMES['PSD'] = "application/x-photoshop";
$_MIMES['EXE'] = "application/binary";
$_MIMES['DLL'] = "application/binary";
$_MIMES['ZIP'] = "application/x-compressed";
**/

/**
* creates a dir in file system optionally creating all pathes on the way
* @param path the relative path from dataroot
* @param recursive if true, creates recursively all path elements
*/
function filesystem_create_dir($path, $recursive = 0) {
   global $CFG;

   $result = true;
   if (!$recursive){
      if ($CFG->debug > 8) mtrace("creating dir <i>{$path}</i><br/>");
      $oldMask = umask(0);
      if(!filesystem_is_dir($path)) $result = @mkdir($CFG->dataroot . '/' . $path, 0777);
      umask($oldMask);
      return $result;
   }
   else {
      $parts = explode('/', $path);
      $pathTo = '';
      for($i = 0; $i < count($parts) && $result; $i++){
         $pathTo .= '/' . $parts[$i];
         $result = filesystem_create_dir($pathTo, 0);
      }
      return $result;
   }
}

/**
* tests if path is a dir. A simple wrapper to is_dir 
* @param relativepath the path from dataroot
*/
function filesystem_is_dir($relativepath){
   global $CFG;

   if ($CFG->debug > 8) mtrace("is dir <i>$relativepath</i><br/>");
   return is_dir($CFG->dataroot . '/' . $relativepath);
} 

/**
* checks if file (or dir) exists. A simple wrapper to file_exists
* @param relativepath the path from dataroot
*/
function filesystem_file_exists($relativepath){
   global $CFG;

   if ($CFG->debug > 8) mtrace("file exists <i>$relativepath</i><br/>");
   return file_exists($CFG->dataroot . '/' . $relativepath);
} 

/**
* scans for entries within a directory
* @param relativepath the path from dataroot
* @param hiddens shows or hide hidden files
* @param what selects only dirs, files or both
* @return an array of entries wich are local names in path
*/
function filesystem_scan_dir($relativepath, $hiddens = 0, $what = 0){
   global $CFG;

   if ($CFG->debug > 8) mtrace("scanning <i>$relativepath</i><br/>");
   $dir = opendir($CFG->dataroot . '/' . $relativepath);
   $entries = array();
   while ($anEntry = readdir($dir)){
      if ($what == FS_ONLY_DIRS){
         $subpath = $relativepath . '/' . $anEntry;
         $subpath = preg_replace("/^\//", "", $subpath);
         if (!filesystem_is_dir($subpath)) continue ;
      }
      if ($what == FS_NO_DIRS){
         $subpath = $relativepath . '/' . $anEntry;
         $subpath = preg_replace("/^\//", "", $subpath);
         if (filesystem_is_dir($subpath)) continue ;
      }
      if ($hiddens) {
         if (($anEntry != '.') && ($anEntry != '..')) $entries[] = $anEntry;
      }
      else {
         if (!preg_match("/^\./", $anEntry)) $entries[] = $anEntry;
      }
   }
   closedir($dir);
   return $entries;
} 

/**
* clears and removes an entire dir
* @param relativepath the path from dataroot
* @param fulldelete if true, removes the dir root either
* @return an array of entries wich are local names in path
*/
function filesystem_clear_dir($relativepath, $fullDelete = false) {
   global $CFG;

   if ($CFG->debug > 8) mtrace("clearing dir <i>$relativepath</i><br/>");
   $exists = filesystem_is_dir($relativepath);
   if (!$exists && !$fullDelete) {
      return filesystem_create_dir($relativepath);   
   }
   if (!$exists && $fullDelete) {
      return true;
   }
   $files = filesystem_scan_dir($relativepath, FS_SHOW_HIDDEN, FS_ALL_ENTRIES);
   foreach($files as $aFile) {
      if ($aFile == "." || $aFile == "..") continue ;
      if (filesystem_is_dir("{$relativepath}/{$aFile}")){
         filesystem_clear_dir("{$relativepath}/{$aFile}", FS_FULL_DELETE);
         // fs_removeDir("{$relativepath}/{$aFile}");
      }
      else
         filesystem_delete_file("{$relativepath}/{$aFile}");
   }
   if (file_exists($CFG->dataroot . '/' . $relativepath) && $fullDelete) return filesystem_remove_dir($relativepath);
   return false;
}

/**
* copies recursively a subtree from a location to another
* @param source the source path from dataroot
* @param dest the dest path from dataroot
* @return void
*/
function filesystem_copy_tree($source, $dest) {
   global $CFG;
   if ($CFG->debug > 8) mtrace("copying tree <i>$source</i> to <i>$dest</i><br/>");
   if (file_exists($dest) && !filesystem_is_dir($dest)) {
      return;
   }
   if (!filesystem_is_dir($dest)) {
      filesystem_create_dir($dest, FS_RECURSIVE);
   }
   $files = array();
   $files = filesystem_scan_dir( $source );
   foreach($files as $aFile) {
      if ($aFile == '.' || $aFile == '..') next;
      if (filesystem_is_dir("{$source}/{$aFile}")) {
         filesystem_create_dir("{$dest}/{$aFile}", FS_NON_RECURSIVE);
         if (count(filesystem_is_dir("{$source}/{$aFile}")) != 0) {
             filesystem_copy_tree("{$source}/{$aFile}", "{$dest}/{$aFile}");
         }
      }
      else {
           filesystem_copy_file("{$source}/{$aFile}", "{$dest}/{$aFile}");
      }
   }
}

/**
* stores a file content in the file system, creating on the way directories if needed
* @param relativepath the path from dataroot
* @param data the data to store in
*/
function filesystem_store_file($relativepath, $data) {
   global $CFG;

   if ($CFG->debug > 8) mtrace("storing <i>$relativepath</i><br/>");
   $parts = pathinfo($relativepath);
   if (!filesystem_is_dir($parts['dirname'])) filesystem_create_dir($parts['dirname']);
   $FILE = fopen($CFG->dataroot . '/' . $relativepath, "w");
   if (!$FILE) return false;
   fwrite ($FILE, $data);
   fclose($FILE);
   return true;
}

/**
* reads a file content and returns scalar string
* @param relativepath the path from dataroot
* @return the data as a string
*/
function filesystem_read_a_file($relativepath) {
   global $CFG;

   if ($CFG->debug > 8) mtrace("reading <i>$relativepath</i><br/>");
   $fullPath = $CFG->dataroot . '/' . $relativepath;
   if (file_exists($fullPath)){
      $FILE = file($fullPath);
      return implode('', $FILE);
   }
   return false;
}

/**
* deletes a file. Simple wrapper to unlink
* @param relativepath the path from dataroot
* @return the data as a string
*/
function filesystem_delete_file($relativepath){
   global $CFG;

   if ($CFG->debug > 8) mtrace("deleting file <i>$relativepath</i><br/>");
   if (filesystem_file_exists($relativepath) && !filesystem_is_dir($relativepath))
      return unlink($CFG->dataroot . '/' . $relativepath);
   return false;
}

/**
* removes an empty dir. Simple wrapper to rmdir
* @param relativepath the path from dataroot
*/
function filesystem_remove_dir($relativepath){
   global $CFG;

   if ($CFG->debug > 8) mtrace("deleting dir <i>$relativepath</i><br/>");
   if (filesystem_file_exists($relativepath))
      return rmdir($CFG->dataroot . '/' . $relativepath);
}

/**
* renames a file. Simple wrapper to rename
* @param relativepath the path from dataroot
*/
function filesystem_move_file($source,$dest){
    global $CFG;

    if (filesystem_file_exists($source)){
        if ($CFG->debug > 8) mtrace("moving file/dir <i>$source</i> to <i>$dest</i><br/>");
        return rename($CFG->dataroot . '/' . $source, $CFG->dataroot . '/' . $dest);
    }
    return false;
}

/**
* copy a file creating all path on the way if needed
* @param source the source path from dataroot
* @param dest the dest path from dataroot
*/
function filesystem_copy_file($source, $dest) {
   global $CFG;

   if ($CFG->debug > 8) mtrace("copying file <i>$source</i> to <i>$dest</i><br/>");
   if (!filesystem_file_exists($source)) return -1;
   $parts = pathinfo($dest);
   if (!filesystem_is_dir($parts['dirname'])) filesystem_create_dir($parts['dirname']);
   return copy($CFG->dataroot . '/' . $source, $CFG->dataroot . '/' . $dest);
}

/**
* gets a filtered list of files
* @param path the path from dataroot
* @param filemask the filemask for filtering
*/
function filesystem_get_file_list($path, $filemask = "*.*") {
   global $CFG;
   
   if (preg_match("/(.*)\/$/", $path, $matches)) $path = $matches[1];
   $files = glob($CFG->dataroot . "{$path}/{$filemask}");
   return $files;
}

/**
TODO should be recoded

function getFileIcon($mimetype){
   switch($mimetype) {
      case 'dir': return '<img src="images/fileicons/DIR.gif">'; break;
      case 'application/x-word': return '<img src="images/fileicons/WORD.gif">'; break;
      case 'application/x-excel': return '<img src="images/fileicons/XLS.gif">'; break;
      case 'application/x-powerpoint': return '<img src="images/fileicons/PPT.gif">'; break;
      case 'application/x-pdf': return '<img src="images/fileicons/PDF.gif">'; break;
      case 'application/x-compressed': return '<img src="images/fileicons/ZIP.gif">'; break;
      case 'application/x-audio': return '<img src="images/fileicons/SND.gif">'; break;
      case 'application/x-photoshop': return '<img src="images/fileicons/PSD.gif">'; break;
      case 'text/html': return '<img src="images/fileicons/HTM.gif">'; break;
      case 'text/diml': return '<img src="images/fileicons/DIM.gif">'; break;
      case 'text/raw': return '<img src="images/fileicons/TXT.gif">'; break;
      case 'image/gif': return '<img src="images/fileicons/GIF.gif">'; break;
      case 'image/jpg': return '<img src="images/fileicons/JPG.gif">'; break;
      case 'image/png': return '<img src="images/fileicons/PNG.gif">'; break;
      case 'image/bmp': return '<img src="images/fileicons/BMP.gif">'; break;
      case 'application/binary': return '<img src="images/fileicons/EXE.gif">'; break;
      default : return '<img src="images/fileicons/FILE.gif">';
   }
}

function getMimeType($extension){
   global $_MIMES;
   if (!array_key_exists(strtoupper($extension), $_MIMES)) return '';
   return $_MIMES[strtoupper($extension)];
}

*/
?>