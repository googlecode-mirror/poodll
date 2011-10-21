<?php

    /** 
    * This view provides a way for editing a poodllpairwork session
    * 
    * @package mod-poodllpairwork
    * @category mod
    * @author Justin Hunt
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

    if (!defined('MOODLE_INTERNAL')){
        error("Illegal direct access to this screen");
    }

    
	include "{$CFG->dirroot}/mod/poodllpairwork/editviewcontroller.php";
 
?>


<center>
<div style="width: 90%">
<form name="session" method="POST" action="view.php">
<input type="hidden" name="what" value="addsession" />
<input type="hidden" name="pairid" value="0" />
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="view" value="edit" />
<?php    
if (!empty($pairs)){
		echo "<center><table width='60%' border=1 bgcolor='#cfcfcf' cellpadding='2' cellspacing='2'>";
		echo "<tr><td colspan='4' forecolor='#ffffff' bgcolor='#x25BA'>" . get_string('pairs', 'poodllpairwork') ." ( " . count($pairs) . " )</td></tr>";
	foreach ($pairs as $pair){

		//echo "<tr><td>" . $pair['A']['fullname'] . "</td><td>" . $pair['B']['fullname'] . "</td>";
		$auser = get_record('user', 'username', $pair['A']['username']);
		$apic = "<img src='" . fetch_user_picture($auser,35) . "'/><br />";
		$buser = get_record('user', 'username', $pair['B']['username']);
		$bpic = "<img src='" . fetch_user_picture($buser,35) . "'/><br />";
		echo "<tr><td align='center'>" . $apic . $pair['A']['fullname'] . "</td><td align='center'>" . $bpic . $pair['B']['fullname'] . "</td>";
		echo "<td>" . fetchTeacherPairBroadcastPlayer($pair['A']['username'] ,$pair['B']['username'],fetch_user_picture($USER,35),fullname($USER)). "</td>";

		?>
		<td><p><input type="button" name="go" value="<?php print_string('clearpair', 'poodllpairwork') ?>" onclick="document.forms['session'].what.value = 'clearpair' ;document.forms['session'].pairid.value = '<?php print $pair['A']['username']?>' ; document.forms['session'].submit()" />
		<?php
	}
	echo "</td></tr></table>"; 

	
?>
</center>
<?php 
	print_simple_box(get_string('allbroadcast', 'poodllpairwork'));
	echo fetchTeacherAllBroadcastPlayer(fetch_user_picture($USER,35),fullname($USER));

?>
<p><input type="button" name="go" value="<?php print_string('clearsession', 'poodllpairwork') ?>" onclick="document.forms['session'].what.value = 'clearsession' ; document.forms['session'].submit()" /></p>

<?php
} else {

    print_simple_box(get_string('nosession', 'poodllpairwork'));
}
	
if (empty($users)){
	print_simple_box(get_string('nousers', 'poodllpairwork'));
}else{
	echo "<center><table width='40%' border=1 bgcolor='#cfcfcf' cellpadding='2' cellspacing='2'>";
	echo "<tr><td colspan='2' forecolor='#ffffff' bgcolor='#x25BA'>" . get_string('unassignedusers', 'poodllpairwork') ." ( " . count($users) . " )</td></tr>";
	$odd=true;
	foreach ($users as $user){			
		$apic = "<img src='" . fetch_user_picture($user,35) . "'/><br />";
		if ($odd){		
			echo "<tr><td width='50%' align='center'>" . $apic . fullname($user) . "</td>";
		}else{
			echo "<td width='50%' align='center'>" . $apic .   fullname($user)  . "</td></tr>";
		}
		$odd = !$odd;
	}
	if (!$odd) echo "</td></tr>";
	echo "</table>";    
	
?>

<p>	<input type="button" name="go" value="<?php print_string('addsession', 'poodllpairwork') ?>" onclick="document.forms['session'].what.value = 'addsession' ; document.forms['session'].submit()" /></p>

<?php
}
?>
<p>	<input type="button" name="go" value="<?php print_string('refreshsessionpage', 'poodllpairwork') ?>" onclick="document.forms['session'].what.value = 'refreshsession' ; document.forms['session'].submit()" /><?php print_string('sessionTTL', 'poodllpairwork') ?><input name="sessionttl" type="text" value="<?php print $ttl ?>" /> </p>
</form>

</div>

