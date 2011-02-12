<?php
/*
*@titel:			Who Vote (Wer hat abgestimmt)
*@version:			2.3
*@autor:			Agi
*@reworked by:		Michael (KleenMicha) Schüler
*@begin				2006-08-12
*@last update		2007-09-06
*@license			GNU/GPL
*/
$filename = 'mod_whovote.php';

if (!$_REQUEST['pollid'] || !$wbbuserdata['a_can_view_whovote']) {
	die('No Access');
}
else $pollid = wbb_trim( $_REQUEST['pollid'] );

$user_votet = '';

$perpage = 20;	// Hier kann die Anzahl an Nutzern pro Seite festgelegt werden, je mehr Benutzer, desto mehr Datenbankabfragen gibt es

$guestview = 1;	// Sollen Gäste mit angezeigt werden?

list($votecount) = $db->query_first("SELECT count(id) FROM bb".$n."_votes WHERE id='".intval($pollid)."'");

if (!isset($_GET['page']) || $_GET['page'] == "") $page = 1;
else $page = (int) $_GET['page'];
if (isset($_REQUEST['ordermode']) && !empty($_REQUEST['ordermode'])) $ordermode = wbb_trim($_REQUEST['ordermode']);
else $ordermode = 1;

$pages = ceil($votecount / $perpage);

switch ($ordermode) {
	case 1: $mode = ' ORDER BY u.username ASC'; break;
	case 2: $mode = ' ORDER BY u.username DESC'; break;
	case 3: $mode = ' ORDER BY v.voteid ASC'; break;
	case 4: $mode = ' ORDER BY v.voteid DESC'; break;
	default: $mode = ' ORDER BY v.userid ASC'; break;
}

$result = $db->query("SELECT v.*, u.username FROM bb".$n."_votes v
LEFT JOIN bb".$n."_users u ON (u.userid = v.userid)
WHERE id='".intval($pollid)."'$mode", $perpage, $perpage * ($page - 1));

$count = (($ordermode) ? (($page-1)*$perpage) : ($votecount +1 - ($page-1)*$perpage));

while ($polls = $db->fetch_array($result)) {
	$polls['voteid'] = explode(",", $polls['voteid']);
	foreach ($polls['voteid'] as $polloptionid) {
		$results = $db->query("SELECT polloption FROM bb".$n."_polloptions WHERE polloptionid='$polloptionid'");
		while ($polloption = $db->fetch_array($results)) {
			if ($polls['userid'] == 0 && $guestview == 1) $polls['userdata'] = 'Gast';
			else $polls['userdata'] = '<a href="profile.php?userid='.$polls['userid'].'">'.$polls['username'].'</a>';

			if ($ordermode == 1) $count++;
			else $count -- ;
			if ($count % 2) $tdclass = 'tablea';
			else $tdclass = 'tableb';

			if ($wbbuserdata['a_can_view_whovote_detailed'])
				eval("\$user_votet .= \"".$tpl->get("thread_whovote_userdetail")."\";");
			else
				eval("\$user_votet .= \"".$tpl->get("thread_whovote_user")."\";");
		}
	}
}
$pages = ceil($votecount/$perpage);
if ($page > $pages) $page = 1;
if ($pages > 1) $pagelink = makePageLink( "thread.php?action=who_vote&amp;pollid=$pollid&amp;ordermode=$ordermode". $SID_ARG_2ND, $page, $pages, $showpagelinks-1 );
else $pagelink = '';
eval("\$tpl->output(\"".$tpl->get("thread_whovote")."\");");
exit();
?>