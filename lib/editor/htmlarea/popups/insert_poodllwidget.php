<?php // $Id: insert_poodllwidget.php,v 1.9 2010/10/13 23:23:44 jhunt $

    require("../../../../config.php");

    $id = optional_param('id', SITEID, PARAM_INT);
    $showrecorder = optional_param('showrecorder', 0, PARAM_BOOL);

    require_login($id);
    require_capability('moodle/course:managefiles', get_context_instance(CONTEXT_COURSE, $id));

	//Get our media resource handling lib
	require_once($CFG->libdir . '/poodllresourcelib.php');


    @header('Content-Type: text/html; charset=utf-8');

    $upload_max_filesize = get_max_upload_file_size($CFG->maxbytes);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php print_string("insertpoodllwidget","poodll");?></title>
<script type="text/javascript" src="popup.js"></script>
<script type="text/javascript">
//<![CDATA[
var preview_window = null;

function Init() {
  __dlg_init();
  var param = window.dialogArguments;
  if (param) {
      document.getElementById("f_url").value = param["f_url"];
      document.getElementById("f_proto").value = param["f_proto"];
      document.getElementById("f_type").value = param["f_type"];
      document.getElementById("f_height").value = param["f_height"];
      document.getElementById("f_width").value = param["f_width"];
      window.ipreview.location.replace('preview_media.php?id='+ <?php print($id);?> +'&mediapath='+ param.f_url);
  }
  document.getElementById("f_url").focus();
};

function onOK() {
  var required = {
    "f_url": "<?php print_string("mustenterurl", "editor");?>"
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  // pass data back to the calling window
  var fields = ["f_url", "f_proto","f_type","f_width","f_height"];
  var param = new Object();
  for (var i in fields) {
    var id = fields[i];
    var el = document.getElementById(id);
    param[id] = el.value;
  }
 if (preview_window) {
    preview_window.close();
  }
  __dlg_close(param);
  return false;
};

function returnFilterString(filtertext) {
  
  // pass data back to the calling window
  
  var param = new Object();
  param['filtertext'] = filtertext;


  __dlg_close(param);
  return false;
};

function onCancel() {
  if (preview_window) {
    preview_window.close();
  }
  __dlg_close(null);
  return false;
};

function onPreview() {
  var f_url = document.getElementById("f_url");
  var path = f_url.value;
  if (!path) {
    alert("<?php print_string("enterurlfirst","editor");?>");
    f_url.focus();
    return false;
  }else{
	//for debugging	
	//alert("got to here:onPreview:insert_poodllwidget.php");
}

  var win = null;
  if (!document.all) {
    win = window.open("about:blank", "ha_imgpreview", "toolbar=no,menubar=no,personalbar=no,innerWidth=100,innerHeight=100,scrollbars=no,resizable=yes");
  } else {
    win = window.open("about:blank", "ha_imgpreview", "channelmode=no,directories=no,height=100,width=100,location=no,menubar=no,resizable=yes,scrollbars=no,toolbar=no");
  }
  preview_window = win;
  var doc = win.document;
  var body = doc.body;
  if (body) {
    body.innerHTML = "";
    body.style.padding = "0px";
    body.style.margin = "0px";
	
	//What to do here: Justin 20090617
	//el is an element, We need the something to use for flash player
    var el = doc.createElement("img");
    el.src = url;

    var table = doc.createElement("table");
    body.appendChild(table);
    table.style.width = "100%";
    table.style.height = "100%";
    var tbody = doc.createElement("tbody");
    table.appendChild(tbody);
    var tr = doc.createElement("tr");
    tbody.appendChild(tr);
    var td = doc.createElement("td");
    tr.appendChild(td);
    td.style.textAlign = "center";

    td.appendChild(el);
    win.resizeTo(el.offsetWidth + 30, el.offsetHeight + 30);
  }
  win.focus();
  return false;
};

function checkvalue(elm,formname) {
    var el = document.getElementById(elm);
    if(!el.value) {
        alert("Nothing to do!");
        el.focus();
        return false;
    }
}

function submit_form(dothis) {
    if(dothis == "delete") {
        window.ibrowser.document.dirform.action.value = "delete";
    }
    if(dothis == "move") {
        window.ibrowser.document.dirform.action.value = "move";
    }
    if(dothis == "zip") {
        window.ibrowser.document.dirform.action.value = "zip";
    }

    window.ibrowser.document.dirform.submit();
    return false;
}

//]]>
</script>
<style type="text/css">
html, body {
margin: 2px;
background-color: rgb(212,208,200);
font-family: Tahoma, Verdana, sans-serif;
font-size: 11px;
}
.title {
background-color: #ddddff;
padding: 5px;
border-bottom: 1px solid black;
font-family: Tahoma, sans-serif;
font-weight: bold;
font-size: 14px;
color: black;
}
td, input, select, button {
font-family: Tahoma, Verdana, sans-serif;
font-size: 11px;
}
button { width: 70px; }
.space { padding: 2px; }
form { margin-bottom: 0px; margin-top: 0px; }
</style>
        <script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/filter/poodll/flash/embed-compressed.js"></script>
</head>
<body onload="Init()">
<?php
echo format_text("{POODLL:type=poodllpalette,width=1100,height=650}");
?>
</body>
</html>
