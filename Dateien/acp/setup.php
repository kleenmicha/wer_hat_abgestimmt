<?php
define("WBB_ACP_LOGIN", true);

$setupfile = 'setup.whovote.xml';
$filename = 'setup.php';

if (file_exists("./lib/install.lock")) die("Bitte l&ouml;schen Sie die Datei ./acp/lib/install.lock, um die Installation erneut ausf&uuml;hren zu k&ouml;nnen!");
if (!file_exists("./$setupfile")) die("Bitte laden Sie die Datei ./acp/$setupfile hoch, um die Installation erneut ausf&uuml;hren zu k&ouml;nnen!");

@error_reporting(7);
@set_time_limit(0);
@set_magic_quotes_runtime(0);
$phpversion = phpversion();

// WBB-Funktionen includen und Datenbank ?ffnen
require("./lib/config.inc.php");
require("./lib/class_db_mysql.php");
require("./lib/functions.php");
require("./lib/admin_functions.php");

$db = new db($sqlhost,$sqluser,$sqlpassword,$sqldb,$phpversion);

class chooseLine {
	function chooseLine ($aa) {
		foreach ($aa as $k=>$v) $this->$k = $aa[$k];
	}
}

/**
 * Reading Line by Line
 *
 * Reading Line by Line of a xml File
 *
 * @author 		Origin by www.php.net
 * @access public
 * @return each line
*/
function readinSetup($setupfile) {
	$handle = @fopen ($setupfile, "r");

	$data = fread ($handle, filesize($setupfile));
	fclose ($handle);

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $tags);
	xml_parser_free($parser);

	foreach ($tags as $key=>$val) {
if ($key == "installer" || $key == "files" || $key == "informel") {
			$molranges = $val;

			for ($i=0; $i < 2; $i+=2) {
				$offset = $molranges[$i] + 1;
				$len = $molranges[$i + 1] - $offset;
$tdb[] = parseLine(array_slice($values, $offset, $len), $key);
			}
		}
		else continue;
	}
	return $tdb;
}

function parseLine($mvalues, $key) {
if ($key == 'files') for ($i=0; $i < count($mvalues); $i++) $mol[$mvalues[$i]["tag"]] .= $mvalues[$i]["value"]. ',';
else for ($i=0; $i < count($mvalues); $i++) $mol[$mvalues[$i]["tag"]] = $mvalues[$i]["value"];
	return new chooseLine($mol);
}

/**
 * Caching Templates
 *
 * @author 		Origin by Burntime
 * @modfied by	KleenMicha 
 * @package 	WoltLab Burning Board
 * @subpackage	Templates
*/
function cacheTemplates() {
	global $db, $n;

	$templateids = '';

	$result = $db->unbuffered_query("SELECT templateid, templatepackid, templatename, recompile FROM bb".$n."_templates");
	while ($row = $db->fetch_array($result)) {
		if ($row['recompile'] == 1 || !file_exists('../cache/templates/'.$row['templatepackid'].'_'.$row['templatename'].'.php')) {
			$templateids .= ','.$row['templateid'];
		}
	}
	
	include_once("./lib/class_templateparser.php");
	if ($templateids) {
		$templateids = wbb_substr( $templateids, 1 );
		$tplparser = new TemplateParser();
		$result = $db->unbuffered_query("SELECT templateid, templatename, templatepackid, template FROM bb".$n."_templates WHERE templateid IN (".$templateids.")");
		while ($row = $db->fetch_array($result)) {
			// parse template
			$template = $tplparser->parse( dos2unix( $row['template'] ) );

			if (@is_file("./../cache/templates/" . $row['templatepackid'] . "_" . $row['templatename'] . ".php") && !@is_writeable("./../cache/templates/" . $row['templatepackid'] . "_" . $row['templatename'] . ".php")) return '<li>Das Template kann nicht gecacht werden. Der Cacheordner ist nicht vorhanden oder besitzt keine Schreibrechte.</li>';

			// cache template
			$fp = @fopen("./../cache/templates/" . $row['templatepackid'] . "_" . $row['templatename'] . ".php", "w+b");
			@fwrite($fp, "<?php
/*
templatepackid: ".$row['templatepackid']."
templatename: ".$row['templatename']."
*/

\$this->templates['".$row['templatename']."']=\"".addcslashes($template, "$\"\\")."\";
?".">");
			@fclose($fp);
			@chmod("./../cache/templates/" . $row['templatepackid'] . "_" . $row['templatename'] . ".php", 0777);
			unset($template);
		}
		$db->unbuffered_query("UPDATE bb".$n."_templates SET recompile = 0 WHERE templateid IN (".$templateids.")", 1);
		return '<li>Die neuen Templates wurden erfolgreich gecacht!</li>';
	}
}

/** die with error message **/
function diewitherror($error="unbekannter Fehler", $title="unbekannter Fehler") {
 echo '<?xml version="1.0" encoding="windows-1252"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de" xml:lang="de">
<head>
<title>' . $title . '</title>
<style type="text/css">
<!--
body {
 color: #000;
 font: 13px Trebuchet MS, sans-serif;
 background: #fff;
}

td { font: 13px Trebuchet MS, sans-serif; }

th { text-align: left; }

a:link, a:visited, a:active {
 color: #F60;
 text-decoration: underline;
}
a:hover {
 color: #F30;
 text-decoration: none;
}
-->
</style>
</head>
<body>
 <table align="center" width="400">
  <tr>
   <td><br /><br />Folgender Fehler ist, beim installieren, aufgetreten.<br />Fehlermeldung: '.$error.'</td>
  </tr>
 </table>
</body>
</html>';
 exit();
}

if(version_compare($phpversion, "4.1.0")==-1) {
 $_REQUEST=array_merge($HTTP_COOKIE_VARS,$HTTP_POST_VARS,$HTTP_GET_VARS);
 $_COOKIE=&$HTTP_COOKIE_VARS;
 $_SERVER=&$HTTP_SERVER_VARS;
 $_FILES=&$HTTP_POST_FILES;
 $_GET=&$HTTP_GET_VARS;
 $_POST=&$HTTP_POST_VARS;
}

if(isset($_REQUEST['step'])) $step = intval($_REQUEST['step']);
else $step = 0;

$do = readinSetup($setupfile);
$title = $do[0]->title;
if ($do[0]->updatefrom == '' ) $installtype = 'Installation';
else $installtype  = 'Update';

print '<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de" xml:lang="de">
<head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
print '<title>'.$title.'</title>';
print '<style type="text/css">
body {
 color: #000;
 font: 13px Trebuchet MS, sans-serif;
 background: #FFF;
}

td { font: 13px Trebuchet MS, sans-serif; }

th { text-align: left; }

a:link, a:visited, a:active {
 color: #F60;
 text-decoration: underline;
}

a:hover {
 color: #F30;
 text-decoration: none;
}

small { font-size: 85%; }

.red { color: #F00; }

.green { color: green; }

.red a:link, .red a:visited, .red a:active {
 color: #F60;
 font-weight: bold;
 text-decoration: underline;
}

.red a:hover {
 color: #F30;
 font-weight: bold;
 text-decoration: none;
}

ul {
 list-style-type: none;
 padding: 5px;
 margin: 10px 0 10px 0;
 border: 1px solid #f00;
 color: #E36363;
}
</style>
<script type="text/javascript">
 function enabledl () {	
  if (document.forms[\'agreeform\'].elements[\'startbutton\'].disabled==true) document.forms[\'agreeform\'].elements[\'startbutton\'].disabled=false;
  else document.forms[\'agreeform\'].elements[\'startbutton\'].disabled=true;
 }
</script>
</head>
<body>
 <table border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
   <td width="100%" align="left">';

if (!$step) {
	// WBB-Version checken
	list($wbbversion) = $db->query_first("SELECT value FROM bb".$n."_options WHERE varname = 'boardversion'");
	$check = 0;
	if (stristr($wbbversion, "pl2")) {
		$wbbversions = explode('pl2', $wbbversion);
		foreach ($wbbversions as $wbbversions) {
			if (version_compare($wbbversions,$do[0]->wbbversion) < 0) {
				$check = 1;
				continue;
			}
		}
		if ($check != 1) {
print '<p>';
if ($installtype == 'Installation') print 'Die Installation';
else print 'Das Update';
print ' erfordert eine WoltLab Burning Board in der Version '.$do[0]->wbbversion.'!</p>';
print '<p>Ihre WBB-Version ist '.$wbbversion.'.</p>';
print '<b>Das Script wird daher abgebrochen wird abgebrochen.</b>';
print '</td></tr></table></body></html>';
exit();
		}
	}
	if ($check == 0 && version_compare($wbbversion,$do[0]->wbbversion) < 0) {
print '<p>';
if ($installtype == 'Installation') print 'Die Installation';
else print 'Das Update';
print ' erfordert eine WoltLab Burning Board in der Version '.$do[0]->wbbversion.'!</p>';
print '<p>Ihre WBB-Version ist '.$wbbversion.'.</p>';
print '<b>Das Script wird daher abgebrochen wird abgebrochen.</b>';
print '</td></tr></table></body></html>';
exit();
	}

print '<h2>'.$title.'</h2><br />';
print $do[0]->description.' f&uuml;r das '.$do[0]->name.' <span class="red">'.$do[0]->version.'</span>!<br /><br />';

	?>
<tr>
 <td><h3><em style="text-decoration: underline;">Voraussetzungen:</em> <span style="font-size: 75%;">(<a href="<?php echo $filename; ?>">erneut pr&uuml;fen</a>)</span></h3></td>
</tr>
<tr>
 <td>
  <table style="width: 100%; margin: 8px; border: 1px solid #cdcdcd; padding: 8px">
   <tr>
    <th style="width: 400px">Eigenschaft</th><th style="width: 350px">aktuell</th><th style="width: 150px">empfohlen</th>
   </tr>
   <tr>
    <td>Alle ben&ouml;tigten Datein vorhanden?<br /><small>(Diese Datein werden alle zum richtigen Funktionieren des Hacks ben&ouml;tigt.)</small></td><td>
	 <?php
	 $files = array();
	 $files = explode(',',$do[1]->filename);
	 $error = '';
	 foreach ($files as $filess) {
	 	if(!file_exists('../'.$filess)) {
			$error .= '<li>./'.$filess.'</li>';
		}
	 }
	 if ($error == '') echo '<span class="green"><b>Ja</b></span>';
else echo '<ul> ' .$error . '</ul><span class="red">nicht vorhanden.</span><br />Lade diese Datein entsprechend ihrer Ordnung hoch';
	 ?>
	</td>
	<td>Ja </td>
   </tr> 
  </table>
  <?php
	$fehlt = '';
if ($do[0]->sqlfile != '' && !is_file($do[0]->sqlfile)){
$fehlt .='<b class="red">Datei '.$do[0]->sqlfile.' fehlt im acp Ordner!</b><br />';
}

if ($do[0]->lngfile != '' && !is_file($do[0]->lngfile)){
$fehlt .='<b class="red">Datei '.$do[0]->lngfile.' fehlt im acp Ordner!</b><br />';
}

if ($do[0]->wbbfile != '' && !is_file($do[0]->wbbfile)){
$fehlt .='<b class="red">Datei '.$do[0]->wbbfile.' fehlt im acp Ordner!</b><br />';
}

if ($do[0]->stylefile != '' && !is_file($do[0]->stylefile)){
$fehlt .='<b class="red">Datei '.$do[0]->stylefile.' fehlt im acp Ordner!</b><br />';
}

	if ($fehlt != '') {
		print '<h3><u>Fehlermeldung!</u></h3>';
		print $fehlt.'<br />';
		print 'Lade diese bitte in den Ordner ./acp, denn ohne sie ist ein weiteres Fortfahren nicht m&ouml;glich.<br />';
		print 'Starte dann dieses '.$installtype.'skript neu!<br /><br /><br />';
		print '</td></tr></table></body></html>';
		exit();
	}
	print '<b>';
	if ($installtype == 'Installation') print 'Die Installation';
	else print 'Das Update';
	print ' kann nun beginnen!</b><br /><br />';
print '<b><a href="./setup.php?step=1" style="color:#090">Beginnen</a></b><br /><br />';
	print '</td></tr></table></body></html>';
print '<ol>';
}

if ($step == 1) {
print '<h2>'.$title.'</h2><br />';
print $do[0]->description.' f&uuml;r das '.$do[0]->name.' <span class="red">'.$do[0]->version.'</span>!<br /><br />';

	if ($do[0]->sqlfile != '') {
		require("./lib/class_query.php");

		$fp = fopen($do[0]->sqlfile, "rb");
		$query = implode("", file($do[0]->sqlfile));
		fclose($fp);
		$sql_query = new query($query);
		$sql_query->doquery();

		print '<li>Die Datenbank wurde erfolgreich erweitert und die Optionen wurden aktualisiert!</li>';
	}

	if ($do[0]->wbbfile != '') {
		require_once("./lib/class_variableimport.php");
		$variableimport = new variableimport($do[0]->wbbfile);
		if ($variableimport->errors()) echo 'Es traten folgende Fehler beim Lesen der Variablendatei auf: <br />'.$variableimport->getErrors().'<br />';
		else {
			$variableimport->import();
			if ($variableimport->errors()) diewitherror("Ung&uuml;ltige Variablendatei: ".$variableimport->getErrors());
		}
		print '<li>Die neuen Gruppenrechte und Menueintr&auml;ge im acp wurden erfolgreich eingef&uuml;gt!</li>';
	}

	if ($do[0]->lngfile != '') {
		$lngdata = readlngfile($do[0]->lngfile, 1);
		$languagepacks = array();
		$cats = array();

		$result = $db->query("SELECT languagepackid FROM bb".$n."_languagepacks");
		while ($row = $db->fetch_array($result)) $languagepacks[] = $row['languagepackid'];
		// install new languagecats
		if (count($lngdata['cats'])){
			$where="";
			foreach ($lngdata['cats'] as $cat){
				$db->unbuffered_query("INSERT IGNORE INTO bb".$n."_languagecats (catname) VALUES ('".addslashes($cat)."')");
				$where .= ",'".addslashes($cat)."'";
			}
			$cats = array();
$result=$db->query("SELECT catid,catname FROM bb".$n."_languagecats WHERE catname IN(".substr($where,1).")");
			while ($row = $db->fetch_array($result)) $cats[$row['catname']]=$row['catid'];
		}
		foreach( $languagepacks as $languagepackid){
			if (count($lngdata['items'])){
				$insert_str="";
				foreach ($lngdata['items'] as $cat=>$itemarray){
					$showorder = 1;
					foreach ($itemarray as $itemname=>$item){
						$insert_str.=",('".$languagepackid."','".$cats[$cat]."', '".addslashes($itemname)."', '".addslashes($item)."', '".$showorder."')";
						$showorder++;
					}
				}
if ($insert_str) $db->unbuffered_query("REPLACE INTO bb".$n."_languages (languagepackid,catid,itemname,item,showorder) VALUES ".substr($insert_str,1), 1);
				foreach ($cats as $catname=>$catid) updateCache( $languagepackid, $catid );
			}
		}
		print '<li>Das bestehende Sprachpaket wurde aktualisiert</li>';	
	}

if ($do[0]->templates == 1) {
	//Import from Templates
	$templatefolder = "./../templates";	
	$templates = array();

	if ($handle = @opendir($templatefolder)) {
		while ($file = readdir($handle)) {
			if (($file == "..") || ($file == ".") || !eregi("thread_whovote",$file)) continue;
			$templates[] = $file;
		}
	}

	if (count($templates)) {
		for ($i = 0; $i < count($templates); $i++) {
			$templatename = wbb_substr( $templates[$i], 0, - 1 * wbb_strlen( strrchr($templates[$i], ".") ) );
			$fp = fopen($templatefolder."/".$templates[$i], "rb");
			$template = dos2unix( @fread($fp, filesize($templatefolder."/".$templates[$i])) );
			fclose($fp);
			$db->unbuffered_query("REPLACE INTO bb".$n."_templates (templatepackid,templatename,template) VALUES ('0','".addslashes($templatename)."','".addslashes($template)."')", 1);
		}
		updateTemplateStructure();
print '<li>Die neuen Templates wurden erfolgreich importiert</li>';
	}
	//cachen der neuen Templates
	$message = cacheTemplates();
	print $message;
}

if ($do[0]->acptemplates == 1) {
	//Cachen der acp-Templates
if (isset($_REQUEST['tplname'])) $tplname = trim($_REQUEST['tplname']);
	else $tplname = "";
		
	if ($tplname && file_exists()) {
		$templates = array($tplname);
	}
	else {
		$templates = array();
		$handle = opendir("./templates");
		while ($file = readdir($handle)) {
if ($file == ".." || $file == "." || substr($file, - 3) != "htm") continue;
$templates[] = substr($file, 0, - 1*strlen(strrchr($file, ".")));
		}
		closedir($handle);
		unset($handle);
		sort($templates);
	}

	include_once("./lib/class_templateparser.php");
	$tplparser = new TemplateParser();

	for ($i = 0; $i < count($templates); $i++){
		$templatename = $templates[$i];
		flush(); 

		$fp = fopen("./templates/".$templatename.".htm", "rb");
		$template = fread($fp, filesize("./templates/".$templatename.".htm"));
		fclose($fp);
		$template = dos2unix( $template );
		$template = $tplparser->parse( $template );
		$fp = fopen("../cache/templates/acp/".$templatename.".php", "w+b");
		fwrite($fp, "<?php
/*
templatepackid: acp template
templatename: ".$templatename."
*/

\$this->templates['acp_".$templatename."']=\"".addcslashes($template, "$\"\\")."\";
?".">");
		fclose($fp);
		@chmod("../cache/templates/acp/".$templatename.".php", 0777);
		@touch("../cache/templates/acp/".$templatename.".php", filemtime("./templates/".$templatename.".htm"));
	}
	print '<li>Die acp-Templates worden erfolgreich gecacht.</li>';
}
print '</ol>';
	print '<b style="color: #390">';
	if ($installtype == 'Installation') print 'Die Installation';
	else print 'Das Update';
print ' des '.$do[0]->name.' ist nun abgeschlossen!<br />Bitte l&ouml;schen Sie die folgenden Dateien:</b>';
	print '<ul><li>'.$filename.'</li>';
	if ($do[0]->sqlfile != '') print '<li>'.$do[0]->sqlfile.'</li>';
	if ($do[0]->wbbfile != '') print '<li>'.$do[0]->wbbfile.'</li>';
	if ($do[0]->lngfile != '') print '<li>'.$do[0]->lngfile.'</li>';
if ($do[0]->stylefile != '') print '<li>'.$do[0]->stylefile.'</li>';
	print '<li>'.$setupfile.'</li>';
	print '</ul>vom Server!';
	print '</td></tr></table></body></html>';
    $fp = @fopen("./lib/install.lock", "w+b");
    fclose($fp);
    exit();	
}
?>