<?php  // $Id: playview.php,v 1.0 2009/02/03 12:23:36 justin Exp $
/**
 * This page prints the play tabview of a poodllpairwork instance
 *
 * @author
 * @version $Id: playview.php,v 1.0 2009/02/03 12:23:36 justin Exp $
 * @package poodllpairwork
 **/



	//to call the poodllpairworkplayer from media resource lib: justin 20090209
	require_once($CFG->libdir . '/poodllresourcelib.php');
	
	//Include backend and form related actions	
    include "{$CFG->dirroot}/mod/poodllpairwork/playviewcontroller.php";

	//If we are sessioning based on usermap then 
	//we should have a pairmap retrieved in playviewcontroller.
	//So here we just fetch the player
	$pairworkplayer="";
	if ($pairwork->sessiontype == SESSIONTYPE_USERNAME){
		//if we have a role and hence a session.
		if ($studentalias != ""){			
			$partner = get_record('user', 'username', $pairmap->partnername);
			$partnerpic = fetch_user_picture($partner,35);
			$mepic = fetch_user_picture($USER,35);
			$pairworkplayer =  "<h4>" . get_string("yourpartneris", "poodllpairwork") . fullname($partner) . "</h4>";
			$pairworkplayer .= fetchPairworkPlayer($pairmap->username,$pairmap->partnername,$mepic, fullname($USER),$partnerpic,fullname($partner));						
		//if we don't have a role and so there is no session(at least not for us).
		}else{
			//To Do Justin 20100522 : 
			//It works but there are no hooks to control the absence presence of whiteboard or chat and the toggling only works for b partner
			//we also still need to fill in the src with a linl to poodlllogiclib to fethc bacht the introa and introb html .
			//srcfor the a user needs to deliver back the equiv of echo format_text($pairwork->introa, FORMAT_HTML);
			//srcfor the b user needs to deliver back the equiv of echo format_text($pairwork->introb, FORMAT_HTML);
			//the sizing of the iframe has not been figured out yet either.
			//==========================================================================================
			//for now you can do most things buy dropping a pairwork filter string in the main pairwork intro
			echo format_text($pairwork->intro, FORMAT_HTML);
			echo "<br/>";
			
			//$pairworkplayer =fetch_pairclient(false,true,false,"",true);
			$pairworkplayer =fetch_embeddablepairclient($CFG->filter_poodll_newpairwidth,$CFG->filter_poodll_newpairheight,'false','true','false',"",true);
			echo $pairworkplayer;

			//echo "<br /><iframe id='poodllrole_a' name='poodllrole_a' src='http://www.moodle.com' style='display: none; width: 100%; height: 600;'>";
			
			//echo "&nbsp;";
			//echo "</iframe>";
			//echo "<br /><iframe id='poodllrole_b' name='poodllrole_b' src='http://www.google.com' style='display: none; width: 100%; height: 600;'>";
			//	echo format_text($pairwork->introb, FORMAT_HTML);
			//echo "&nbsp;";
			//echo "</iframe>";
			
			echo "<br /><div id='poodllrole_a' name='poodllrole_a' style='display: none;'>";
			echo format_text($pairwork->introa, FORMAT_HTML);
			echo "</div>";

			echo "<br /><div id='poodllrole_b' name='poodllrole_b' style='display: none;'>";
			echo format_text($pairwork->introb, FORMAT_HTML);
			echo "</div>";
			
			return;
		}
	}
		
		
		//depending on A or B or neither, return the correct text on the page
		switch (strtolower($studentalias)){
			case "a":
				echo format_text($pairwork->intro, FORMAT_HTML);
				echo "<br/>";
				if ($pairworkplayer!=""){				
					echo $pairworkplayer;	
				}
				echo "<br/>";
				echo format_text($pairwork->introa, FORMAT_HTML);
				break;
			case "b":
				echo format_text($pairwork->intro, FORMAT_HTML);
				echo "<br/>";
				if ($pairworkplayer!=""){				
					echo $pairworkplayer;	
				}
				echo "<br/>";
				echo format_text($pairwork->introb, FORMAT_HTML);
				break;
			default:
				
				echo "<center><h3>" . get_string("ChooseAB", "poodllpairwork") . "</h3></center>";
				echo " <TABLE BORDER='0' CELLPADDING='2' CELLSPACING='0' WIDTH='450' ALIGN='center' BGCOLOR='#a3c2ff'>";
				echo " <tr><td align='center'>";	
				echo "<a href='view.php?id=$cm->id&studentalias=a'><img src='images/AButton.png' alt='A Button' /></a>";
				echo "</td><td align='center'>";
				echo "<a href='view.php?id=$cm->id&studentalias=b'><img src='images/BButton.png' alt='B Button' /></a>";
				echo "</td></tr></table>";
				
		}			


?>
