<?php  //$Id: filtersettings.php,v 0.0.0.1 2010/01/15 22:40:00 thomw Exp $

$items = array();

$items[] = new admin_setting_heading('filter_poodll_settings', get_string('settings', 'poodll'), '');

$items[] = new admin_setting_configtext('filter_poodll_servername', get_string('servername', 'poodll'), '', 'poodll.com');
$items[] = new admin_setting_configtext('filter_poodll_serverid', get_string('serverid', 'poodll'), '', 'poodll');
$items[] = new admin_setting_configtext('filter_poodll_serverport', get_string('serverport', 'poodll'), '', '1935', PARAM_INT);


//$items[] = new admin_setting_configtext('filter_poodll_buffer', get_string('buffer', 'poodll'), '', 0, PARAM_INT);
//$items[] = new admin_setting_configcheckbox('filter_poodll_repeat', get_string('repeat','poodll'), '', 0);
//$items[] = new admin_setting_configcheckbox('filter_poodll_allowfs', get_string('allowfs', 'poodll'), '', 0);
//$items[] = new admin_setting_configcheckbox('filter_poodll_autostart', get_string('autostart', 'poodll'), '', 0);

	//audio player and capture settings.	
$items[] = new admin_setting_configtext('filter_poodll_audiowidth', get_string('audiowidth', 'poodll'), '', '320', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_audioheight', get_string('audioheight', 'poodll'), '', '25', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_studentmic', get_string('studentmic', 'poodll'), '', '');
$items[] = new admin_setting_configtext('filter_poodll_micrate', get_string('micrate', 'poodll'), '','22', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_micsilencelevel', get_string('micsilencelevel', 'poodll'), '', '10', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_micgain', get_string('micgain', 'poodll'), '', '50', PARAM_INT); 
$items[] = new admin_setting_configtext('filter_poodll_micecho', get_string('micecho', 'poodll'), '', 'yes');
$items[] = new admin_setting_configtext('filter_poodll_micloopback', get_string('micloopback', 'poodll'), '', 'no');

	
		//video player and capture settings.
$items[] = new admin_setting_configtext('filter_poodll_studentcam', get_string('studentcam', 'poodll'), '', '');
$items[] = new admin_setting_configtext('filter_poodll_videowidth', get_string('videowidth', 'poodll'), '', '320', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_videoheight', get_string('videoheight', 'poodll'), '', '240', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_capturewidth', get_string('capturewidth', 'poodll'), '', '320', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_captureheight', get_string('captureheight', 'poodll'), '', '240', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_capturefps', get_string('capturefps', 'poodll'), '', '17', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_bandwidth', get_string('bandwidth', 'poodll'), '', '0', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_picqual', get_string('picqual', 'poodll'), '', '5', PARAM_INT);
	
	
$items[] = new admin_setting_configtext('filter_poodll_talkbackwidth', get_string('talkbackwidth', 'poodll'), '', '760', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_talkbackheight', get_string('talkbackheight', 'poodll'), '', '380', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_showwidth', get_string('showwidth', 'poodll'), '', '750', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_showheight', get_string('showheight', 'poodll'), '', '480', PARAM_INT);

$items[] = new admin_setting_configtext('filter_poodll_biggallwidth', get_string('biggallwidth', 'poodll'), '', '850', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_biggallheight', get_string('biggallheight', 'poodll'), '', '680', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_smallgallwidth', get_string('smallgallwidth', 'poodll'), '', '450', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_smallgallheight', get_string('smallgallheight', 'poodll'), '', '320', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_newpairwidth', get_string('newpairwidth', 'poodll'), '', '750', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_newpairheight', get_string('newpairheight', 'poodll'), '', '480', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_whiteboardwidth', get_string('wboardwidth', 'poodll'), '', '600', PARAM_INT);
$items[] = new admin_setting_configtext('filter_poodll_whiteboardheight', get_string('wboardheight', 'poodll'), '', '350', PARAM_INT);



//$items[] = new admin_setting_configcheckbox('filter_poodll_forum_recording', get_string('forum_recording', 'poodll'), '', 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_forum_audio', get_string('forum_audio', 'poodll'), '', 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_forum_video', get_string('forum_video', 'poodll'), '', 1);

//$items[] = new admin_setting_configcheckbox('filter_poodll_journal_recording', get_string('journal_recording', 'poodll'), '', 0);
$items[] = new admin_setting_configcheckbox('filter_poodll_journal_audio', get_string('journal_audio', 'poodll'), '', 1);
$items[] = new admin_setting_configcheckbox('filter_poodll_journal_video', get_string('journal_video', 'poodll'), '', 1);

//$items[] = new admin_setting_configcheckbox('filter_poodll_useproxy', get_string('useproxy', 'poodll'), '', 0);

$items[] = new admin_setting_configcheckbox('filter_poodll_usecourseid', get_string('usecourseid', 'poodll'), '', 1);
$items[] = new admin_setting_configtext('filter_poodll_filename', get_string('filename', 'poodll'), '', 'poodll_file.flv');
$items[] = new admin_setting_configcheckbox('filter_poodll_overwrite', get_string('overwrite', 'poodll'), '', 1);

$items[] = new admin_setting_configtext('filter_poodll_screencapturedevice', get_string('screencapturedevice', 'poodll'), '', 'none');


//bandwidth settings (how close is the poodll server ...) / pic qual 1 - 100


foreach ($items as $item) {
    $item->set_updatedcallback('filter_tex_updatedcallback');
    $settings->add($item);
}

?>
