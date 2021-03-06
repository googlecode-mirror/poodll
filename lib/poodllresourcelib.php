<?php  // $Id: poodllresourcelib.php,v 1.119.2.13 2008/07/10 09:48:44 scyrma Exp $
/**
 * Code for clients(voice recorder etc)  to use when handling poodllresources
 *
 *
 * @author Justin Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/**
 * Show a mediaplayer loaded with a media
 *
 * @param integer $mediaid The id of the media to show
 */
 
define('TEACHERSTREAMNAME','voiceofauthority');
//some constants for the type of media  resource
define('MR_TYPEVIDEO',0);
define('MR_TYPEAUDIO',1);
define('MR_TYPETALKBACK',2);
 
require_once($CFG->dirroot . '/filter/poodll/poodllinit.php');
require_js ($CFG->httpswwwroot . '/mod/assignment/type/poodllonline/swfobject.js');
require_js ($CFG->httpswwwroot . '/mod/assignment/type/poodllonline/javascript.php');


function fetch_slidemenu(){
	global $CFG, $USER, $COURSE;

	if (!empty($USER->username)){
		$mename=$USER->username;
	}else{
		$mename="guest_" + rand(100000, 999999);
	}

	$flvserver = $CFG->poodll_media_server;
	$homeurl = $CFG->wwwroot ;
	$courseid =$COURSE->id;

	

		$partone= '<script type="text/javascript">
						lzOptions = { ServerRoot: \'\'};
				</script>';
		$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
		$partthree =	'<script type="text/javascript">
				lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/slidemenu.lzx.swf9.swf?bcolor=0xFF0000&lzproxied=false&slidewidth=247&slideheight=96&red5url='.urlencode($flvserver). 
							'&homeurl=' . $homeurl .  '&courseid=' . $courseid .  
							'&lzproxied=false\', bgcolor: \'#cccccc\', width: \'400\', height: \'96\', id: \'lzapp_slide_' . rand(100000, 999999) . '\', accessible: \'false\'});       
			</script>
			<noscript>
				Please enable JavaScript in order to use this application.
			</noscript>';
		
		return $partone . $parttwo . $partthree;

}


function fetch_poodllconsole($coursedataurl="",$mename="", $courseid=-1, $embed=false){
	global $CFG, $USER, $COURSE;
	
	$broadcastkey="1234567";

	//Set the camera prefs
	$capturewidth=$CFG->filter_poodll_capturewidth;
	$captureheight=$CFG->filter_poodll_captureheight;
	$capturefps=$CFG->filter_poodll_capturefps;
	$prefcam=$CFG->filter_poodll_screencapturedevice;
	$prefmic=$CFG->filter_poodll_studentmic;
	$bandwidth=$CFG->filter_poodll_bandwidth;
	$picqual=$CFG->filter_poodll_picqual; 
	$cameraprefs= '&capturefps=' . $capturefps . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth .   '&prefmic=' . $prefmic . '&prefcam=' . $prefcam;
	$flvserver = $CFG->poodll_media_server;
	$teacherpairstreamname="voiceofauthority";


	if ($mename=="" && !empty($USER->username)){
		$mename=$USER->username;
		$mefullname=fullname($USER);
		$mepictureurl=fetch_user_picture($USER,35);
	}

	//if courseid not passed in, try to get it from global
	if ($courseid==-1){
		$courseid=$COURSE->id;
	}
	
	//put in a coursedataurl if we need one
	if ($coursedataurl=="") $coursedataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php%3F';
	
	
	//Show the buttons window if we are admin
	//Also won't receive messages intended for students if we are admin. Be aware.
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$am="admin";
	}else{
		$am="0";
	}
/*
		$partone= '<script type="text/javascript">
						lzOptions = { ServerRoot: \'\'};
				</script>';
		$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
		$partthree =	'<script type="text/javascript">
				lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/poodllconsole.lzx.swf?&bcolor=0xFF0000&lzproxied=false&red5url='.urlencode($flvserver). 
							'&mename=' . $mename . '&courseid=' . $COURSE->id .  
							"&teacherpairstreamname=$teacherpairstreamname" . 
							"&coursedataurl=$cousedataurl&broadcastkey=$broadcastkey&capturedevice=$capturedevice" .
							"&capturesizeindex=$capturesizeindex" .
							'&lzr=swf9&lzproxied=false\', bgcolor: \'#cccccc\', width: \'1\', height: \'1\', id: \'lzapp_poodllheader_' . rand(100000, 999999) . '\', accessible: \'false\'});       
			</script>
			<noscript>
				Please enable JavaScript in order to use this application.
			</noscript>';
		
		return $partone . $parttwo . $partthree;
		*/

		//here we setup the url and params for the admin console
		$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/poodllconsole.lzx.swf9.swf';
		$params= '?red5url='.urlencode($flvserver). 
							'&mename=' . $mename . '&courseid=' . $courseid .  
							'&teacherpairstreamname=' . $teacherpairstreamname . 
							$cameraprefs .
							'&coursedataurl=' . $coursedataurl . '&broadcastkey=' . $broadcastkey .
							'&lzr=swf9&runtime=swf9';

		//if we are embedding, here we wrap the url and params in the necessary javascript tags
		//otherwise we just return the url and params.
		//embed code is called from poodlladminconsole.php
		if($embed){
				$partone= '<script type="text/javascript">lzOptions = { ServerRoot: \'\'};</script>';
				$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
				$partthree='<script type="text/javascript">lz.embed.swf({url: \'' . $baseUrl . $params. 
						'\' , width: \'1000\', height: \'750\', id: \'lzapp_admin_console\', accessible: \'false\'});
							</script>
						<noscript>
							Please enable JavaScript in order to use this application.
						</noscript>';
				return $partone . $parttwo . $partthree;
		}else{
			return $baseUrl . $params;					
		}				

}


function fetch_clientconsole($coursedataurl, $courseid=-1, $embed=false){
	global $CFG, $USER, $COURSE;
	
	$broadcastkey="1234567";
	$capturedevice = $CFG->filter_poodll_screencapturedevice;
	$flvserver = $CFG->poodll_media_server;
	$teacherpairstreamname="voiceofauthority";
	
	if (!empty($USER->username)){
		$mename=$USER->username;
		$mefullname=fullname($USER);
		$mepictureurl=fetch_user_picture($USER,35);
	}else{
		//this is meaningless currently, guest access is unlikely to work
		$mename="guest_" + rand(100000, 999999);
		$mefullname="guest";
		$mepictureurl="";
	}
	
	//if courseid not passed in, try to get it from global
	if ($courseid==-1){
		$courseid=$COURSE->id;
	}
	
	//Show the buttons window if we are admin
	//Also won't receive messages intended for students if we are admin. Be aware.
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $courseid))){		
		$am="admin";
	}else{
		$am="0";
	}

		$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/clientconsole.lzx.swf9.swf';
		$params= '?lzproxied=false&red5url='.urlencode($flvserver). 
							'&mename=' . $mename . '&courseid=' . $courseid .  
							"&teacherpairstreamname=$teacherpairstreamname" . 
							"&coursedataurl=$coursedataurl&broadcastkey=$broadcastkey&capturedevice=$capturedevice" .
							"&mefullname=$mefullname" .
							"&mepictureurl=$mepictureurl" .
							"&showwidth=100" .
							"&showheight=100" .
							'&lzproxied=false';
		//if we are embedding, here we wrap the url and params in the necessary javascript tags
		//otherwise we just return the url and params.
		//embed code is called from poodllclientconsole.php
		if($embed){
				$partone= '<script type="text/javascript">lzOptions = { ServerRoot: \'\'};</script>';
				$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
				$partthree='<script type="text/javascript">lz.embed.swf({url: \'' . $baseUrl . $params. 
						'\' , width: \'100%\', height: \'100%\', id: \'lzapp_client_console\', accessible: \'false\'});
							</script>
						<noscript>
							Please enable JavaScript in order to use this application.
						</noscript>';
				return $partone . $parttwo . $partthree;
		}else{
			return $baseUrl . $params;					
		}

}

function fetch_poodllheader(){
	global $CFG, $USER, $COURSE;

	if (!empty($USER->username)){
		$mename=$USER->username;
	}else{
		$mename="guest_" + rand(100000, 999999);
	}
	$coursedataurl=$CFG->wwwroot . "/lib/poodlllogiclib.php";
	$flvserver = $CFG->poodll_media_server;
	$bcsturl =urlencode(fetch_screencast_subscribe($mename));
	//$clnturl =urlencode(fetch_clientconsole($coursedataurl,,false));
	$clnturl =urlencode($CFG->wwwroot . '/lib/' . 'poodllclientconsole.php?coursedataurl=' . urlencode($coursedataurl) . '&courseid=' . $COURSE->id);
	$bcstadmin =urlencode(fetch_screencast_broadcast($mename));
	$pairsurl =urlencode(fetch_pairclient($mename));
	$interviewurl=urlencode(fetch_interviewclient($mename));
	$jumpurl=urlencode(fetch_jumpmaker($mename));
	$showwidth=$CFG->filter_poodll_showwidth;
	$showheight=$CFG->filter_poodll_showheight;
	
	//Show the buttons window if we are admin
	//Also won't receive messages intended for students if we are admin. Be aware.
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$am="admin";
	}else{
		$am="0";
	}

		$partone= '<script type="text/javascript">
						lzOptions = { ServerRoot: \'\'};
				</script>';
		$parttwo = '<script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>';
		$partthree =	'<script type="text/javascript">
				lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/poodllheader.lzx.swf9.swf?bcolor=0xFF0000&lzproxied=false&red5url='.urlencode($flvserver). 
							'&mename=' . $mename . '&courseid=' . $COURSE->id .  '&clnturl=' . $clnturl . '&bcsturl=' . $bcsturl . '&bcstadmin=' . $bcstadmin . '&pairsurl=' . $pairsurl . '&interviewurl=' . $interviewurl . '&jumpurl=' . $jumpurl . '&broadcastheight=' . $showheight . 
							'&lzproxied=false\', bgcolor: \'#cccccc\', width: \'2\', height: \'2\', id: \'lzapp_poodllheader_' . rand(100000, 999999) . '\', accessible: \'false\'});       
			</script>
			<noscript>
				Please enable JavaScript in order to use this application.
			</noscript>';
		
		return $partone . $parttwo . $partthree;

}


//this is the code to get the embed code for the poodllpairwork client
//We separate the embed and non embed into two functions 
//unlike with clientconsole and adminconsole, because of the need for width and height params.
function fetch_embeddablepairclient($width,$height,$chat,$whiteboard, $showvideo,$whiteboardback,$useroles=false){
global $CFG;
//laszlo client expects "true" or "false"  so this line is defunct. Thoug we need to standardise how we do this. 
//$showvideo = ($showvideo=="true");
 return('
        <script type="text/javascript">
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript">
              lz.embed.swf({url: \'' . fetch_pairclient($chat,$whiteboard, $showvideo,$whiteboardback,$useroles) . '\', bgcolor: \'#cccccc\', width: \''. $width . '\', height: \'' . $height .'\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ');      

}

//this is the code to get a poodllpairwork client for display without embedding
//in the poodll header section of a moodle page as an inline page, or in a popup
function fetch_pairclient($chat=true, $whiteboard=true, $showvideo=false,$whiteboardback="", $useroles=false){
	global $CFG, $USER, $COURSE;
	
	if (!empty($USER->username)){
		$mename=$USER->username;
		$mefullname=fullname($USER);
		$mepictureurl=fetch_user_picture($USER,120);
	}else{
		//this is meaningless currently, there is no current way to do pairs
		//with guest. Lets call it "casual poodllpairwork." Butin future it is possible
		$mename="guest_" + rand(100000, 999999);
		$mefullname="guest";
		$mepictureurl="";
	}
	
	//Set the servername
	$flvserver = $CFG->poodll_media_server;
	
	//Work out the course id to use and the url stub for the imageurl
	if ($CFG->filter_poodll_usecourseid){
		$basefile= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" ;
		$courseid=$COURSE->id;
	}else{
		$basefile= $CFG->wwwroot . "/file.php/" ;
		$courseid="";
	}

	//Complete the image url
	if ($whiteboardback != ""){$whiteboardback = $basefile . $whiteboardback;}


	$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/newpairclient.lzx.swf10.swf';
	$params = '?red5url='.urlencode($flvserver) . '&mename=' . $mename . '&mefullname=' . $mefullname . '&mepictureurl=' . $mepictureurl 
			. '&chat=' . $chat  . '&useroles=' . $useroles  . '&whiteboard=' . $whiteboard . '&whiteboardback=' . $whiteboardback . '&showvideo=' . $showvideo  . '&courseid=' . $COURSE->id .'&teacherallstreamname=voiceofauthority&lzproxied=false';
	return $baseUrl . $params;	
}

//this is a stub which we will need to fill in later 
//with the real code
function fetch_interviewclient(){
	return "";
}



//Audio playlist player with defaults, for use with directories of audio files
function fetchSpellingPlayer($wordset, $protocol="", $width="600",$height="350"){
global $CFG, $USER, $COURSE;

//Set our servername .
$flvserver = $CFG->poodll_media_server;
$datadir= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" ;

//determine which of, automated or manual playlists to use
if(strlen($wordset) > 4 && substr($wordset,-4)==".xml"){
	//get a manually made playlist
	$fetchdataurl= $datadir . $wordset;
}else{
	//get the url to the automated medialist maker
	$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=spellinglist&courseid=' . $COURSE->id 
		. '&paramone=' . $wordset 
		. '&cachekiller=' . rand(10000,999999);
}

	

	//some common variables for the embedding stage.	
	$playerLoc = $CFG->wwwroot . '/filter/poodll/flash/spelling.lzx.swf9.swf';

		$returnString = " <table><tr><td>
	        <script type=\'text/javascript\'>
	            lzOptions = { ServerRoot: \'\'};
	        </script>
	        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
	        <script type=\"text/javascript\">
	" . '	lz.embed.swf({url: \'' . $playerLoc . '?red5url='.urlencode($flvserver).
		'&protocol=' . $protocol . 
		'&textwidth=' . 500 .
		'&textheight=' . 60 .
		'&fsize=' . 40 .
		'&datadir=' . urlencode($datadir) . 
		'&wordset='.urlencode($fetchdataurl).
		'&lzproxied=false\', bgcolor: \'#ffffff\', width: \'' . $width . 
		'\', height: \''. $height . '\', id: \'lzapp_spellingplayer_' . rand(100000, 999999) . '_ap\' , accessible: \'false\'});			
	' . "

	        </script>
	        <noscript>
	            Please enable JavaScript in order to use this application.
	        </noscript>
	        </td></tr>
			<tr><td></td></tr></table>";

	
	return $returnString; 
	
}



//this is a stub which we will need to fill in later 
//with the real code
function fetch_jumpmaker(){
	global $CFG, $USER;
	
	if (!empty($USER->username)){
		$mename=$USER->username;
	}else{
		$mename="guest_" + rand(100000, 999999);
	}
	
	//Set the servername
	$flvserver = $CFG->poodll_media_server;


	$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/jumpmaker.lzx.swf';
	$params = '?red5url='.urlencode($flvserver) . '&mename=' . $mename;
	return $baseUrl . $params;	
}

function fetch_poodllpalette($width=500, $height=300){
global $CFG, $USER, $COURSE;
//Set the servername
$flvserver = $CFG->poodll_media_server;

//get the main url of the poodllpallette
$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/poodllpalette.lzx.swf9.swf';
$coursefilesurl = $CFG->wwwroot . '/lib/editor/htmlarea/poodll-coursefiles.php?id=' . $COURSE->id;
$componentlist = $CFG->wwwroot . '/filter/poodll/flash/componentlist.xml';
$poodlllogicurl = $CFG->wwwroot . '/lib/poodlllogiclib.php';

//Set the camera prefs
$capturewidth=$CFG->filter_poodll_capturewidth;
$captureheight=$CFG->filter_poodll_captureheight;
$capturefps=$CFG->filter_poodll_capturefps;
$prefcam=$CFG->filter_poodll_studentcam;
$prefmic=$CFG->filter_poodll_studentmic;
$bandwidth=$CFG->filter_poodll_bandwidth;
$picqual=$CFG->filter_poodll_picqual; 
$cameraprefs= '&capturefps=' . $capturefps . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth .   '&prefmic=' . $prefmic . '&prefcam=' . $prefcam;

$params = '?red5url='.urlencode($flvserver). '&poodlllogicurl=' . $poodlllogicurl . $cameraprefs . '&course='.$COURSE->id . '&filename=amediafile.flv&coursefiles=' . urlencode($coursefilesurl) .'&componentlist=' . urlencode($componentlist);
//return $baseUrl . $params;	

 return('
        <script type="text/javascript">
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript">
              lz.embed.swf({url: \'' . $baseUrl . $params . '\', bgcolor: \'#cccccc\', width: \''. $width . '\', height: \'' . $height . '\', id: \'lzapp_palette\', accessible: \'false\'});
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        '); 	
}

function fetch_screencast_subscribe($mename="", $embed=false, $width=600, $height=350,$broadcastkey="1234567"){
global $CFG, $USER, $COURSE;
//Set the servername
$flvserver = $CFG->poodll_media_server;


//get my name
if($mename==""){$mename=$USER->username;}

//Set  the display sizes
$showwidth=$width;
if($showwidth==0){$showwidth=$CFG->filter_poodll_showwidth;}

$showheight=$height;
if($showheight==0){$showheight=$CFG->filter_poodll_showheight;}

//get the main url of the screensubcribe client
$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/screensubscribe.lzx.swf9.swf';
$params = '?red5url='.urlencode($flvserver). '&broadcastkey='.$broadcastkey. '&showwidth='.$showwidth. '&showheight='.$showheight.'&courseid='.$COURSE->id  .'&mename='.$mename;
//return $baseUrl . $params;	

//if necessary return the embed code, otherwise just return the url
if (!$embed){
	return $baseUrl . $params;
}else{
 return('
        <script type="text/javascript">
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript">
              lz.embed.swf({url: \'' . $baseUrl . $params . '\', bgcolor: \'#cccccc\', width: \''. ($showwidth+10) . '\', height: \'' . ($showheight+10) .'\', id: \'lzapp_screensubscribe_' . rand(100000, 999999) . '\', accessible: \'false\'});
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        '); 	
}

}
function fetch_screencast_broadcast($mename){
global $CFG, $USER, $COURSE;

//Set the servername
$flvserver = $CFG->poodll_media_server;
$broadcastkey="1234567";
$capturedevice = $CFG->filter_poodll_screencapturedevice;

	$baseUrl = $CFG->wwwroot . '/filter/poodll/flash/screenbroadcast.lzx.swf';
	$params = '?red5url='.urlencode($flvserver). '&broadcastkey='.$broadcastkey. '&capturedevice='.$capturedevice. '&mename='.$mename;
	return $baseUrl . $params;	
}
 
function fetch_teachersrecorder($filename="", $updatecontrol){
global $CFG, $USER, $COURSE;

//Set the servername
$flvserver = $CFG->poodll_media_server;
if ($filename == ""){
 $filename = $CFG->filter_poodll_filename;
 }

//Set the camera prefs
$capturewidth=$CFG->filter_poodll_capturewidth;
$captureheight=$CFG->filter_poodll_captureheight;
$capturefps=$CFG->filter_poodll_capturefps;
$prefcam=$CFG->filter_poodll_studentcam;
$prefmic=$CFG->filter_poodll_studentmic;
$bandwidth=$CFG->filter_poodll_bandwidth;
$picqual=$CFG->filter_poodll_picqual; 
$cameraprefs= '&capturefps=' . $capturefps . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth .   '&prefmic=' . $prefmic . '&prefcam=' . $prefcam;
 
 
//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
if ($CFG->filter_poodll_usecourseid){
	$courseid = $COURSE->id;
}else{
	$courseid = -1;
}

 return('
        <script type="text/javascript">
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type="text/javascript" src="' . $CFG->wwwroot . '/filter/poodll/flash/embed-compressed.js"></script>
        <script type="text/javascript">
              lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/PoodLLTeachersRecorder.lzx.swf9.swf?red5url='.urlencode($flvserver). '&updatecontrol='.$updatecontrol. '&course='.$courseid. '&filename='.$filename. $cameraprefs . '&lzproxied=false\', bgcolor: \'#cccccc\', width: \'430\', height: \'360\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ');        
       // echo "\n";

}



function fetch_whiteboard($boardname, $imageurl="", $slave=false,$rooms="", $width=600,$height=350, $mode='normal',$standalone='false'){
global $CFG, $USER,$COURSE;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//Work out the course id to use and the url stub for the imageurl
if ($CFG->filter_poodll_usecourseid){
	$basefile= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" ;
	$courseid=$COURSE->id;
}else{
	$basefile= $CFG->wwwroot . "/file.php/" ;
	$courseid="";
}

//Complete the image url
if ($imageurl != ""){$imageurl = $basefile . $imageurl;}

//If standalone, then lets standalonify it
if($standalone == 'true'){
	$boardname="solo";
}


//Determine if we are admin, if necessary , for slave/master mode
	if ($slave && has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$slave=false;
	}

//whats my name...? my name goddamit, I can't remember  N A mm eeeE
$mename=$USER->username;		

//Are  we merely a slave to the admin whiteboard ?
//(aren't we all, sigh....)
if ($slave){
	$widgetstring= $CFG->wwwroot . '/filter/poodll/flash/scribbleslave.lzx.swf9.swf?&red5url='.urlencode($flvserver).'&courseid='.$courseid.'&mename='.$mename . '&boardname=' . $boardname . '&imageurl=' . $imageurl;
}else{
	//normal mode is a standard scribble with a cpanel
	//simple mode has a simple double click popup menu
	if ($mode=='normal'){
		$widgetstring= $CFG->wwwroot . '/filter/poodll/flash/scribbler.lzx.swf9.swf?&red5url='.urlencode($flvserver).'&courseid='.$courseid.'&mename='.$mename. '&rooms=' . $rooms . '&boardname=' . $boardname . '&imageurl=' . $imageurl;
	}else{
		$widgetstring= $CFG->wwwroot . '/filter/poodll/flash/simplescribble.lzx.swf9.swf?&red5url='.urlencode($flvserver).'&courseid='.$courseid.'&mename='.$mename. '&rooms=' . $rooms . '&boardname=' . $boardname . '&imageurl=' . $imageurl;
	}
}

 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $widgetstring . '&lzproxied=false\', bgcolor: \'#ffffff\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '_whiteboard\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		<tr><td></td></tr></table>");  
		

}








function fetchTalkbackPlayer($descriptor_file, $streamtype="rtmp",$recordable="false",$savefolder="default"){
global $CFG, $USER,$COURSE;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//for now these are fixed, but in future we might add the assignment id to the fileroot and turn off the randomnames
//then it would be reviewable again in the future by the students.
$fileroot= "moddata/talkbackstreams/"  . $savefolder;
if($CFG->filter_poodll_overwrite){
		$randomfnames="false";
	}else{
		$randomfnames="true";
	}


//We need a filepath stub, just in case for http streaming
//and for fetching splash screens from data directory
//We also need a stub for course id, 0 if we are not using it.
//If we are recording we need an rtmp stream
//and that needs to know the course id (or lack of)

if ($CFG->filter_poodll_usecourseid){
	$basefile= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" ;
	$courseid=$COURSE->id . "/";
}else{
	$basefile= $CFG->wwwroot . "/file.php/" ;
	$courseid="";
}

		


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/talkback.lzx.swf9.swf?red5url='.urlencode($flvserver).'&basefile='.$basefile. '&recordable=' . $recordable . '&fileroot=' . $fileroot . '&randomfnames=' . $randomfnames. '&courseid='.$courseid. '&username=' . $USER->id . '&streamtype='.$streamtype.'&mediadescriptor=' . $basefile . $descriptor_file.'&lzproxied=false\', bgcolor: \'#ffffff\', width: \''. $CFG->filter_poodll_talkbackwidth. '\', height: \'' . $CFG->filter_poodll_talkbackheight . '\', id: \'lzapp_' . rand(100000, 999999) . '_talkback\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		<tr><td></td></tr></table>");  
		

}

function fetchSimpleAudioRecorder($assigname, $userid="", $updatecontrol="saveflvvoice", $filename=""){
global $CFG, $USER, $COURSE;

//Set the servername 
$flvserver = $CFG->poodll_media_server;
	
//Set the microphone config params
$micrate = $CFG->filter_poodll_micrate;
$micgain = $CFG->filter_poodll_micgain;
$micsilence = $CFG->filter_poodll_micsilencelevel;
$micecho = $CFG->filter_poodll_micecho;
$micloopback = $CFG->filter_poodll_micloopback;
$micdevice = $CFG->filter_poodll_studentmic;

	
	

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
if ($CFG->filter_poodll_usecourseid){
	$courseid = $COURSE->id;
}else{
	$courseid = -1;
}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
if ($userid=="") $userid = $USER->username;

//Stopped using this 
//$filename = $CFG->filter_poodll_filename;
 $overwritemediafile = $CFG->filter_poodll_overwrite==1 ? "true" : "false" ;
if ($updatecontrol == "saveflvvoice"){
	$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
}else{
	$savecontrol = "";
}
 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/PoodLLAudioRecorder.lzx.swf9.swf?red5url='.urlencode($flvserver).
		'&rate='. $micrate. '&gain='. $micgain .  '&prefdevice='. $micdevice .  '&loopback='. $micloopback . '&echosuppression='. $micecho . '&silencelevel='. $micsilence .
		'&overwritefile='. $overwritemediafile . '&filename='.$filename. '&assigName=' .$assigname . '&course='.$courseid. '&updatecontrol=' . 
		$updatecontrol . '&uid='.$userid. '&lzproxied=false\', bgcolor: \'#cfcfcf\', width: \'430\', height: \'220\', id: \'lzapp_' . 
		rand(100000, 999999) . '_cr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		<tr><td>"
			. $savecontrol .
"</td></tr></table>");  
		

}

function fetch_stopwatch($width, $height, $fontheight,$mode='normal',$permitfullscreen=false,$uniquename='uniquename'){
global $CFG, $USER, $COURSE;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
if ($CFG->filter_poodll_usecourseid){
	$courseid = $COURSE->id;
}else{
	$courseid = -1;
}

//get username automatically
$userid = $USER->username;


	
	//Determine if we are admin, if necessary , for slave/master mode
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$isadmin=true;
	}else{
		$isadmin=false;
	}
	
	//LZ string if master/save  mode and not admin => show slave mode
	//otherwise show stopwatch
	if ($mode=='master' && !$isadmin) {
		$lzstring= 'lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/slaveview.lzx.swf9.swf?fontheight=' . $fontheight . 
		'&mode=' . $mode . 
		'&permitfullscreen=' . $permitfullscreen . 
		'&red5url='.urlencode($flvserver).
		'&mename=' . $userid . 
		'&courseid=' . $courseid .  
		'&uniquename=' . $uniquename .
		'&lzproxied=false\', allowfullscreen: \'true\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_slaveview_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});';		
	}else{
		$lzstring= 'lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/stopwatch.lzx.swf9.swf?fontheight=' . $fontheight . 
		'&mode=' . $mode .  
		'&permitfullscreen=' . $permitfullscreen . 
		'&red5url='.urlencode($flvserver).
		'&mename=' . $userid . 
		'&courseid=' . $courseid . 
		'&uniquename=' . $uniquename .
		'&lzproxied=false\', allowfullscreen: \'true\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_swatch_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});';	
	}
	
	
	
	



 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . 	$lzstring . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");  
		

}

function fetch_poodllcalc($width, $height){
global $CFG;


 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/poodllcalc.lzx.swf9.swf?lzproxied=false\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_calc_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");  
		

}



function fetch_countdowntimer($initseconds, $usepresets, $width, $height, $fontheight,$mode='normal',$permitfullscreen=false,$uniquename='uniquename'){
global $CFG, $USER, $COURSE;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
if ($CFG->filter_poodll_usecourseid){
	$courseid = $COURSE->id;
}else{
	$courseid = -1;
}

//get username automatically
$userid = $USER->username;


	
	//Determine if we are admin, if necessary , for slave/master mode
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$isadmin=true;
	}else{
		$isadmin=false;
	}
	
		//LZ string if master/save  mode and not admin => show slave mode
	//otherwise show stopwatch
	if ($mode=='master' && !$isadmin) {
		$lzstring= 'lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/slaveview.lzx.swf9.swf?fontheight=' . $fontheight . 
		'&mode=' . $mode . 
		'&permitfullscreen=' . $permitfullscreen . 
		'&red5url='.urlencode($flvserver).
		'&mename=' . $userid . 
		'&courseid=' . $courseid .  
		'&uniquename=' . $uniquename .
		'&lzproxied=false\', allowfullscreen: \'true\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_slaveview_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});';		
	}else{
		$lzstring= 'lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/countdowntimer.lzx.swf9.swf?fontheight=' . $fontheight . 
		'&mode=' . $mode .  
		'&permitfullscreen=' . $permitfullscreen . 
		'&red5url='.urlencode($flvserver).
		'&initseconds='. $initseconds .
		'&usepresets='. $usepresets .
		'&mename=' . $userid . 
		'&courseid=' . $courseid . 
		'&uniquename=' . $uniquename .
		'&lzproxied=false\', allowfullscreen: \'true\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_swatch_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});';	
	}
	
	
	
	



 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . 	$lzstring . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");   
		

}

function fetch_counter($initcount, $usepresets, $width, $height, $fontheight,$permitfullscreen=false){
global $CFG;


 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/counter.lzx.swf9.swf?initcount='. $initcount . '&permitfullscreen=' . $permitfullscreen . '&usepresets=' . $usepresets . '&fontheight=' . $fontheight .'&lzproxied=false\', allowfullscreen: \'true\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");  
		

}

function fetch_dice($dicecount,$dicesize,$width,$height){
global $CFG;


 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/dice.lzx.swf9.swf?dicecount='. $dicecount . '&dicesize=' . $dicesize  .'&lzproxied=false\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");  
		

}

function fetch_flashcards($cardset,$cardwidth,$cardheight,$randomize,$width,$height){
global $CFG,$COURSE;


	//determine which of, automated or manual cardsets to use
	if(strlen($cardset) > 4 && substr($cardset,-4)==".xml"){
		//get a manually made playlist
		$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $COURSE->id . "/" . $cardset;
	}else{
		//get the url to the automated medialist maker
		$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=poodllflashcards&courseid=' . $COURSE->id 
			. '&paramone=' . $cardset 
			. '&cachekiller=' . rand(10000,999999);
	}



 return("
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/flashcards.lzx.swf9.swf?cardset='. urlencode($fetchdataurl) . '&randomize=' . $randomize . '&cardwidth=' . $cardwidth  . '&cardheight=' . $cardheight .  '&lzproxied=false\', width: \'' . $width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        ");  
		

}


function fetchSimpleVideoRecorder($assigname, $userid="", $updatecontrol="saveflvvoice", $filename=""){
global $CFG, $USER, $COURSE;

//Set the servername and a capture settings from config file
$flvserver = $CFG->poodll_media_server;
$capturewidth=$CFG->filter_poodll_capturewidth;
$captureheight=$CFG->filter_poodll_captureheight;
$capturefps=$CFG->filter_poodll_capturefps;
$prefcam=$CFG->filter_poodll_studentcam;
$prefmic=$CFG->filter_poodll_studentmic;
$bandwidth=$CFG->filter_poodll_bandwidth;
$picqual=$CFG->filter_poodll_picqual;

//If we are using course ids then lets do that
//else send -1 to widget (ignore flag)
if ($CFG->filter_poodll_usecourseid){
	$courseid = $COURSE->id;
}else{
	$courseid = -1;
}

//If no user id is passed in, try to get it automatically
//Not sure if  this can be trusted, but this is only likely to be the case
//when this is called from the filter. ie not from an assignment.
if ($userid=="") $userid = $USER->username;

//Stopped using this 
//$filename = $CFG->filter_poodll_filename;
 $overwritemediafile = $CFG->filter_poodll_overwrite==1 ? "true" : "false" ;
if ($updatecontrol == "saveflvvoice"){
	$savecontrol = "<input name='saveflvvoice' type='hidden' value='' id='saveflvvoice' />";
}else{
	$savecontrol = "";
}
 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/PoodLLVideoRecorder.lzx.swf9.swf?&red5url='.urlencode($flvserver).'&overwritefile='. $overwritemediafile .'&capturefps=' . $capturefps . '&filename='.$filename. '&assigName=' .$assigname . '&captureheight=' . $captureheight . '&picqual=' . $picqual . '&bandwidth=' . $bandwidth . '&capturewidth=' . $capturewidth .   '&prefmic=' . $prefmic . '&prefcam=' . $prefcam  . '&course='.$courseid. '&updatecontrol=' . $updatecontrol . '&uid='.$userid. '&lzproxied=false\', width: \'350\', height: \'400\', id: \'lzapp_' . rand(100000, 999999) . '_svr\', accessible: \'false\'});			
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		<tr><td>"
			. $savecontrol .
"</td></tr></table>");  
		

}

//Audio playltest player with defaults, for use with directories of audio files
function fetchAudioTestPlayer($playlist, $protocol="", $width="400",$height="150"){
global $CFG, $USER, $COURSE;

//Set our servername .
$flvserver = $CFG->poodll_media_server;


//determine which of, automated or manual playlists to use
if(strlen($playlist) > 4 && substr($playlist,-4)==".xml"){
	//get a manually made playlist
	$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $courseid . "/" . $playlist;
}else{
	//get the url to the automated medialist maker
	$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=poodllaudiolist&courseid=' . $COURSE->id 
		. '&paramone=' . $playlist 
		. '&paramtwo=' . $protocol 
		. '&cachekiller=' . rand(10000,999999);
}

	




	//some common variables for the embedding stage.	
	$playerLoc = $CFG->wwwroot . '/filter/poodll/flash/poodllaudiotestplayer.lzx.swf9.swf';

		$returnString = " <table><tr><td>
	        <script type=\'text/javascript\'>
	            lzOptions = { ServerRoot: \'\'};
	        </script>
	        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
	        <script type=\"text/javascript\">
	" . '	lz.embed.swf({url: \'' . $playerLoc . '?red5url='.urlencode($flvserver).
		'&playertype=' . $protocol . '&playlist='.urlencode($fetchdataurl).'&lzproxied=false\', bgcolor: \'#ffffff\', width: \'' . $width . 
		'\', height: \''. $height . '\', id: \'lzapp_audioplayer_' . rand(100000, 999999) . '_ap\' , accessible: \'false\'});			
	' . "
	        </script>
	        <noscript>
	            Please enable JavaScript in order to use this application.
	        </noscript>
	        </td></tr>
			<tr><td></td></tr></table>";

	
	return $returnString; 
	
}


//Audio playlist player with defaults, for use with directories of audio files
function fetchAudioListPlayer($playlist, $protocol="", $width="400",$height="350",$sequentialplay="true"){
global $CFG, $USER, $COURSE;

//Set our servername .
$flvserver = $CFG->poodll_media_server;


//determine which of, automated or manual playlists to use
if(strlen($playlist) > 4 && substr($playlist,-4)==".xml"){
	//get a manually made playlist
	$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $courseid . "/" . $playlist;
}else{
	//get the url to the automated medialist maker
	$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=poodllaudiolist&courseid=' . $COURSE->id 
		. '&paramone=' . $playlist 
		. '&paramtwo=' . $protocol 
		. '&cachekiller=' . rand(10000,999999);
}

	




	//some common variables for the embedding stage.	
	$playerLoc = $CFG->wwwroot . '/filter/poodll/flash/poodllaudiolistplayer.lzx.swf9.swf';

		$returnString = " <table><tr><td>
	        <script type=\'text/javascript\'>
	            lzOptions = { ServerRoot: \'\'};
	        </script>
	        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
	        <script type=\"text/javascript\">
	" . '	lz.embed.swf({url: \'' . $playerLoc . '?red5url='.urlencode($flvserver).
		'&sequentialplay=' .$sequentialplay .
		'&playertype=' . $protocol . 
		'&playlist='.urlencode($fetchdataurl).
		'&lzproxied=false\', bgcolor: \'#ffffff\', width: \'' . $width . 
		'\', height: \''. $height . '\', id: \'lzapp_audioplayer_' . rand(100000, 999999) . '_ap\' , accessible: \'false\'});			
	' . "
	        </script>
	        <noscript>
	            Please enable JavaScript in order to use this application.
	        </noscript>
	        </td></tr>
			<tr><td></td></tr></table>";

	
	return $returnString; 
	
}

//Audio player with defaults, for use with PoodLL filter
function fetchSimpleAudioPlayer($rtmp_file, $protocol="", $width="450",$height="40",$embed=false, $embedstring="Play"){
global $CFG, $USER, $COURSE;

//Set our servername .
$flvserver = $CFG->poodll_media_server;

	//Set our use protocol type
	//if one was not passed, then it may have been tagged to the url
	//this was the old way.
	if ($protocol==""){
		$type = "rtmp";
		if (strlen($rtmp_file) > 5){
			$protocol = substr($rtmp_file,0,5);
			switch ($protocol){
				case "yutu:":
					$rtmp_file = substr($rtmp_file,5);
					$rtmp_file = getYoutubeLink($rtmp_file);
					$type="http";
					break;			
				case "http:":
					$rtmp_file = substr($rtmp_file,5);
					$type="http";
					break;		
				case "rtmp:":
					$rtmp_file = substr($rtmp_file,5);
				default:
					$type="rtmp";				

			}
		
		}//end of if strlen(rtmpfile) > 4

	//If we have one passed in, lets set it to our type
	}else{
		switch ($protocol){
				case "yutu":
					$rtmp_file = getYoutubeLink($rtmp_file);
					$type="http";
					break;			
				case "http":
				case "rtmp":
				default:
					$type=$protocol;				

			}
	}

	//Add course id and full path to url if necessary	
	if ($protocol != "yutu"){
		//If we are using course id's add one to this file
		if ($CFG->filter_poodll_usecourseid){
			$rtmp_file= $COURSE->id . "/" . $rtmp_file;
		}

		//If we are http and not youtube, lets set the full path
		if ($type=='http'){
			$rtmp_file= $CFG->wwwroot . "/file.php/" .   $rtmp_file ;
		}
	}


	//some common variables for the embedding stage.	
	$playerLoc = $CFG->wwwroot . '/filter/poodll/flash/poodllaudioplayer.lzx.swf9.swf';

	//If we want to avoid javascript we do it this way
	//embedding via javascript screws updating the entry on the page,
	//which is seen after marking a single audio assignment from a list
	if ($embed ){
		$lzid = "lzapp_audioplayer_" . rand(100000, 999999) ;
		$returnString="		
		 <div id='$lzid' class='player'>
        <a href='#' onclick=\"javascript:loadAudioPlayer('$rtmp_file', '$lzid', 'sample_$lzid', '$width', '$height'); return false;\">$embedstring </a>
      </div>		
		";
	/*
		$flashvars='red5url='.urlencode($flvserver).'&playertype=' . $type . '&mediapath='.$rtmp_file.'&lzproxied=false';
		$returnString  = "<embed 
			  src='$playerLoc' 
			  width='$width'
			  height='$height'
			  bgcolor='#ffffff'
			  allowscriptaccess='always'
			  allowfullscreen='false'
			  flashvars='$flashvars' 				 
				/>";
				*/

			return $returnString;

	//if we do not want to use embedding, ie use javascript to detect and insert (probably best..?)	
	}else{
		$returnString = " <table><tr><td>
	        <script type=\'text/javascript\'>
	            lzOptions = { ServerRoot: \'\'};
	        </script>
	        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
	        <script type=\"text/javascript\">
	" . '	lz.embed.swf({url: \'' . $playerLoc . '?red5url='.urlencode($flvserver).
		'&playertype=' . $type . '&mediapath='.$rtmp_file.'&lzproxied=false\', bgcolor: \'#ffffff\', width: \'' . $width . 
		'\', height: \''. $height . '\', id: \'lzapp_audioplayer_' . rand(100000, 999999) . '_ap\' , accessible: \'false\'});			
	' . "
	        </script>
	        <noscript>
	            Please enable JavaScript in order to use this application.
	        </noscript>
	        </td></tr>
			<tr><td></td></tr></table>";

	}
	return $returnString; 
	
}



//Video player with defaults, for use with PoodLL filter
function fetchSimpleVideoPlayer($rtmp_file, $width="400",$height="380",$protocol="",$embed=false,$permitfullscreen=false, $embedstring="Play"){
global $CFG, $USER, $COURSE;

//Set our servername .
$flvserver = $CFG->poodll_media_server;


	//Massage the media file name if we have a username variable passed in.	
	//This allows us to show different video to each student
	$rtmp_file = str_replace( "@@username@@",$USER->username,$rtmp_file);
	
	//Determine if we are admin, admins can always fullscreen
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$permitfullscreen='true';
	}


	//Set our use protocol type
	//if one was not passed, then it may have been tagged to the url
	//this was the old way.
	if ($protocol==""){
		$type = "rtmp";
		if (strlen($rtmp_file) > 5){
			$protocol = substr($rtmp_file,0,5);
			switch ($protocol){
				case "yutu:":
					$rtmp_file = substr($rtmp_file,5);
					$type="yutu";
					break;			
				case "http:":
					$rtmp_file = substr($rtmp_file,5);
					$type="http";
					break;		
				case "rtmp:":
					$rtmp_file = substr($rtmp_file,5);
				default:
					$type="rtmp";				

			}
		
		}//end of if strlen(rtmpfile) > 4

	//If we have one passed in, lets set it to our type
	}else{
		switch ($protocol){
				case "yutu":		
				case "http":
				case "rtmp":
				default:
					$type=$protocol;				

			}
	}
	
	
	

	//Add course id and full path to url if necessary	
	if ($protocol != "yutu"){
		//If we are using course id's add one to this file
		if ($CFG->filter_poodll_usecourseid){
			$rtmp_file= $COURSE->id . "/" . $rtmp_file;
		}

		//If we are http and not youtube, lets set the full path
		if ($type=='http'){
			$rtmp_file= $CFG->wwwroot . "/file.php/" .   $rtmp_file ;
		}
	}

	//If we want to avoid loading 20 players on the screen, we use this script
	//to load players ondemand
	//this does screw up updating the entry on the page,
	//which is seen after marking a single audio/vide assignment and returning to the list
	//poodllonline assignment
	if ($embed){
		$lzid = "lzapp_videoplayer_" . rand(100000, 999999) ;
		$returnString="		
	  <div id='$lzid' class='player'>
        <a href='#' onclick=\"javascript:loadVideoPlayer('$rtmp_file', '$lzid', 'sample_$lzid', '$width', '$height'); return false;\">$embedstring </a>
      </div>		
		";
	

			return $returnString;

	}else{
		 return("
				<table><tr><td>
				<script type=\'text/javascript\'>
					lzOptions = { ServerRoot: \'\'};
				</script>
				<script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
				<script type=\"text/javascript\">
		" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/poodllvideoplayer.lzx.swf9.swf?&red5url='.urlencode($flvserver).'&playertype=' . $type . '&permitfullscreen=' . $permitfullscreen . '&mediapath='.$rtmp_file.'&lzproxied=false\', allowfullscreen: \'true\', bgcolor: \'#ffffff\', width: \'' . $width . '\', height: \''. $height . '\', id: \'lzapp_' . rand(100000, 999999) . '_jvp\' , accessible: \'false\'});			
		' . "
				</script>
				<noscript>
					Please enable JavaScript in order to use this application.
				</noscript>
				</td></tr>
				<tr><td></td></tr></table>"); 
		}
	
}

function fetchPairworkPlayer($mename,$themname,$mepicurl,$mefullname,$thempicurl,$themfullname){
global $CFG, $USER;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//set the stream name for the teacher to talk to the pair. (should be the same for both a and b, hence the strcmp)
if(strcmp($mename,$themname)<0){
	$teacherpairstreamname=$mename . "_" . $themname;
}else{
	$teacherpairstreamname=$themname . "_" . $mename;
}
 


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/pairbroadcast.lzx.swf?&red5url='.urlencode($flvserver)
.'&mename='.$mename . '&mefullname=' .$mefullname . '&themfullname=' .$themfullname . '&mepictureurl='.urlencode($mepicurl)
. '&thempictureurl='.urlencode($thempicurl).'&themname=' .$themname . '&teacherallstreamname=' . TEACHERSTREAMNAME . '&teacherpairstreamname=' . $teacherpairstreamname
. '&debug=false&lzproxied=false\', bgcolor: \'#D5FFFA\', width: \'300\', height: \'60\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		
		
}

function fetchPairsList($pairdataurl, $offlinedataurl, $unassigneddataurl,$mename){
global $CFG, $USER;
//Set the servername 
$flvserver = $CFG->poodll_media_server;

 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">		
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/pairlist.lzx.swf?&red5url='.urlencode($flvserver) . '&mename=' .$mename . '&pairdataurl='.urlencode($pairdataurl) . '&offlinedataurl='.urlencode($offlinedataurl) . '&unassigneddataurl='.urlencode($unassigneddataurl) 
. '&debug=false&lzproxied=false\', bgcolor: \'#AAAAFF\', width: \'900\', height: \'600\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		

}

function fetchTeacherPairBroadcastPlayer($aname,$bname,$mepicurl,$mefullname){
global $CFG, $USER;

//Set the servername 
$flvserver = $CFG->poodll_media_server;

//set the stream name for the teacher to talk to the pair. (should be the same for both a and b, hence the strcmp)
if(strcmp($aname,$bname)<0){
	$teacherpairstreamname=$aname . "_" . $bname;
}else{
	$teacherpairstreamname=$bname . "_" . $aname;
}
 


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/teacherpairbroadcast.lzx.swf?&red5url='.urlencode($flvserver) . '&teacherpairstreamname=' . $teacherpairstreamname 
.'&mefullname=' .$mefullname . '&mepictureurl='.urlencode($mepicurl) . '&aname=' . $aname . '&bname=' . $bname 
. '&debug=false&lzproxied=false\', bgcolor: \'#AAFFFA\', width: \'110\', height: \'35\', id: \'lzapp_' . rand(100000, 999999) . 'teachertopair' . $teacherpairstreamname . '\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		
		
}

function fetchTeacherAllBroadcastPlayer($mepicurl,$mefullname){
global $CFG, $USER;

//Set the servername 
$flvserver = $CFG->poodll_media_server;
 


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/teacherallbroadcast.lzx.swf?&red5url='.urlencode($flvserver) . '&teacherstreamname=' . TEACHERSTREAMNAME .'&mefullname=' .$mefullname . '&mepictureurl='.urlencode($mepicurl). '&debug=false&lzproxied=false\', bgcolor: \'#AAFFFA\', width: \'145\', height: \'65\', id: \'lzapp_' . rand(100000, 999999) . 'teachertoall\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		
		
}

/**
 * Show a mediaplayer loaded with a media
 * For use as a media resource from DB with options set
 *
 * @param integer $mediaid The id of the media to show
 */
function fetch_mediaplayer($mediaid,$usefileid=""){
global $CFG, $USER, $COURSE;

 	// get accessed by card
    $sql = "
        SELECT
           reference servername, alltext mediafile, options 
        FROM
            {$CFG->prefix}resource       
        WHERE
            id = ".        $mediaid;
    
	$theResource = get_record_sql($sql);     
       
		$id = 'filter_flv_'.time(); //we need something unique because it might be stored in text cache
                $cleanurl = addslashes_js($theResource->servername);
				
	//Massage the media file name if we have a username variable passed in . (A Justin special)		
		//This allows us to show different video to each student
		$mediafile = str_replace( "@@username@@",$USER->username,$theResource->mediafile);
		if($usefileid!=""){
			$mediafile = str_replace( "@@usefileid@@",$usefileid,$theResource->mediafile);
		}
		
	

		
					

	//Set the file location
	$flvserver = $theResource->servername;



	//see if we have data in options
	$options = $theResource->options;
	if (!empty($options)){

	$mediaoptions = explode(';',$options);
			$mediatype = $mediaoptions[0];
			$videowidth = $mediaoptions[1];
			$videoheight = $mediaoptions[2];
			$videobuffer =  $mediaoptions[3];
			$autostart=  $mediaoptions[4]=="1" ? "true" : "false";
			$repeat=  $mediaoptions[5]=="1" ? "true" : "false";
			$allowfullscreen=  $mediaoptions[6]=="1" ? "true" : "false";	

	}else{

			$videowidth = $CFG->filter_poodll_videowidth;
			$videoheight = $CFG->filter_poodll_videoheight;
			//$videobuffer =  $CFG->filter_poodll_buffer;
			//$autostart=  $CFG->filter_poodll_autostart==1 ? "true" : "false";
			//$repeat=  $CFG->filter_poodll_repeat==1 ? "true" : "false";
			//$allowfullscreen=  $CFG->filter_poodll_allowfs==1 ? "true" : "false";

	}


	//Jump in here if it is a talkback, fetch the talkbackplayer 
	if ($mediatype == MR_TYPETALKBACK){
		return fetchTalkbackPlayer($mediafile);
	}

	//Jump in here if it is an video, fetch PoodLLVideoPlayer
	if ($mediatype == MR_TYPEVIDEO){
		return fetchSimpleVideoPlayer($mediafile,$videowidth,$videoheight);
	}

	//Jump in here if it is an audio, fetch poodllaudioplayer
	if ($mediatype == MR_TYPEAUDIO){
		return fetchSimpleAudioPlayer($mediafile,"",$videowidth,$videoheight);
	}
		
}

/**
 * Fetch a list of poodll resources for use in a drop down lost
 * * Deprecated Justin 20100315 ****
 * @param integer $courseid The id of the coursefromwhich to pull the poodllresources
 */
function fetch_medialist($courseid){
global $CFG;

$sql = "
	        SELECT
	           id, name
	        FROM
	            {$CFG->prefix}resource       
	        WHERE
	            type = 'poodll 
		    AND course={$courseid}        
	    ";
	    $resources = get_records_sql($sql);
		
		
		$FLV_Files = array();
	
	//Add a default item to our array
	$FLV_TYPES[0] = get_string('nopoodllresource', 'poodll');
    if (!empty($resources)){
	    foreach($resources as $aResource){
	 
	        $FLV_TYPES[$aResource->id] = $aResource->name;
	    }
	}
		  
	return $FLV_TYPES;
}

    	// $url - the location of video at YouTube, something like http://youtube.com/watch?v=XXX
	//  * * Deprecated Justin 20100315 ****
    function getYoutubeLink($url)
    {
	global $CFG;
	 //load our little library
	require_once($CFG->libdir . '/class.YouTubeParser.php');
	    $parser = new YouTubeParser();
		if ($CFG->filter_poodll_useproxy){
			$parser->setProxy($CFG->proxyhost,$CFG->proxyport);
		}

		//massage the url
		$url = trim(stripslashes($url));
		    //get the link if we can
		if (!($link = $parser->getVideoLink($url))) {
			echo $parser->errMsg;
			return "";
		} else {
		        //save it to a file if we got it
			return urlencode($link);
		}
	
	
	
    }
	
//Given a user object, return the url to a picture for that user.
function fetch_user_picture($user,$size){
global $CFG;

	//get default sizes for non custom pics
    if (empty($size)) {
		//size = 35;
        $file = 'f2';        
    } else if ($size === true or $size == 1) {
        //size = 100;
		$file = 'f1';        
    } else if ($size >= 50) {
        $file = 'f1';
    } else {
        $file = 'f2';
    }
	
	//now get the url for the pic
    if ($user->picture) {  // Print custom user picture
        require_once($CFG->libdir.'/filelib.php');
        $src = get_file_url($user->id.'/'.$file.'.jpg', null, 'user');
    } else {         // Print default user pictures (use theme version if available)
        $src =  "$CFG->pixpath/u/$file.png";
    }
	return $src;
}

function fetch_filter_properties($filterstring){
	//this just removes the {POODLL: .. } to leave us with the good stuff.	
	//there MUST be a better way than this.
	$rawproperties = explode ("{POODLL:", $filterstring);
	$rawproperties = $rawproperties[1];
	$rawproperties = explode ("}", $rawproperties);	
	$rawproperties = $rawproperties[0];

	//Now we just have our properties string
	//Lets run our regular expression over them
	//string should be property=value,property=value
	//got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
	$regexpression='/([^=,]*)=("[^"]*"|[^,"]*)/';
	$matches; 	

	//here we match the filter string and split into name array (matches[1]) and value array (matches[2])
	//we then add those to a name value array.
	$itemprops = array();
	if (preg_match_all($regexpression, $rawproperties,$matches,PREG_PATTERN_ORDER)){		
		$propscount = count($matches[1]);
		for ($cnt =0; $cnt < $propscount; $cnt++){
			// echo $matches[1][$cnt] . "=" . $matches[2][$cnt] . " ";
			$itemprops[$matches[1][$cnt]]=$matches[2][$cnt];
		}
	}

	return $itemprops;

}

function fetchSmallVideoGallery($playlist, $protocol, $width, $height,$permitfullscreen=false){
global $CFG, $USER, $COURSE;

//Set the servername 
$courseid= $COURSE->id;
$flvserver = $CFG->poodll_media_server;

//set size params
if ($width==''){$width=$CFG->filter_poodll_smallgallwidth;}
if ($height==''){$height=$CFG->filter_poodll_smallgallheight;}

//Determine if we are admin, admins can always fullscreen
	if (has_capability('mod/quiz:preview', get_context_instance(CONTEXT_COURSE, $COURSE->id))){		
		$permitfullscreen='true';
	}


//determine which of, automated or manual playlists to use
if(strlen($playlist) > 4 && substr($playlist,-4)==".xml"){
	//get a manually made playlist
	$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $courseid . "/" . $playlist;
}else{
	//get the url to the automated medialist maker
	$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=poodllmedialist&courseid=' . $courseid 
		. '&paramone=' . $playlist 
		. '&paramtwo=' . $protocol 
		. '&cachekiller=' . rand(10000,999999);
}
 


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/smallvideogallery.lzx.swf9.swf?&red5url='.urlencode($flvserver)
	.'&playlist='. urlencode($fetchdataurl)
	. '&permitfullscreen=' . $permitfullscreen 
	.'&playertype='. urlencode($protocol)
	. '&debug=false&lzproxied=false\', bgcolor: \'#D5FFFA\', allowfullscreen: \'true\', width: \'' .$width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		
		
}

function fetchBigVideoGallery($playlist, $protocol, $width, $height){
global $CFG, $USER, $COURSE;

//Set the servername 
$courseid= $COURSE->id;
$flvserver = $CFG->poodll_media_server;

//set size params
if ($width==''){$width=$CFG->filter_poodll_biggallwidth;}
if ($height==''){$height=$CFG->filter_poodll_biggallheight;}

//determine which of, automated or manual playlists to use
if(strlen($playlist) > 4 && substr($playlist,-4)==".xml"){
	//get a manually made playlist
	$fetchdataurl= $CFG->wwwroot . "/file.php/" .  $courseid . "/" . $playlist;
}else{
	//get the url to the automated medialist maker
	$fetchdataurl= $CFG->wwwroot . '/lib/poodlllogiclib.php?datatype=poodllmedialist&courseid=' . $courseid 
		. '&paramone=' . $playlist 
		. '&paramtwo=' . $protocol 
		. '&cachekiller=' . rand(10000,999999);
}


 


 return("
        <table><tr><td>
        <script type=\'text/javascript\'>
            lzOptions = { ServerRoot: \'\'};
        </script>
        <script type=\"text/javascript\" src=\"{$CFG->wwwroot}/filter/poodll/flash/embed-compressed.js\"></script>
        <script type=\"text/javascript\">
" . '	lz.embed.swf({url: \'' . $CFG->wwwroot . '/filter/poodll/flash/bigvideogallery.lzx.swf9.swf?&red5url='.urlencode($flvserver)
	.'&playlist='. urlencode($fetchdataurl)
	. '&debug=false&lzproxied=false\', bgcolor: \'#D5FFFA\', width: \'' .$width . '\', height: \'' . $height . '\', id: \'lzapp_' . rand(100000, 999999) . '\', accessible: \'false\'});	
		
' . "
        </script>
        <noscript>
            Please enable JavaScript in order to use this application.
        </noscript>
        </td></tr>
		</table>");
		
		
}


//WMV player with defaults, for use with PoodLL filter
function fetchWMVPlayer($wmv_file, $width="400",$height="380"){
global $CFG, $USER, $COURSE;

	//Massage the media file name if we have a username variable passed in.	
	//This allows us to show different video to each student
	$wmv_file = str_replace( "@@username@@",$USER->username,$wmv_file);



	//Add course id and full path to url 
	$wmv_file= $CFG->wwwroot . "/file.php/" . $COURSE->id . "/" .   $wmv_file ;

	
		 return("
				<table><tr><td> 
					<object id='MediaPlayer' width=$width height=$height classid='CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95' standby='Loading Windows Media Player components...' type='application/x-oleobject' codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112'>
						<param name='filename' value='$wmv_file'>
						<param name='Showcontrols' value='True'>
						<param name='autoStart' value='False'>
						<param name='wmode' value='transparent'>
						<embed type='application/x-mplayer2' src='$wmv_file' name='MediaPlayer' autoStart='True' wmode='transparent' width='$width' height='$height' ></embed>
					</object>										
				</td></tr></table>"); 
		
	
}




?>
