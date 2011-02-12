<?xml version="1.0" encoding="{$lang->items['LANG_GLOBAL_ENCODING']}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{$lang->items['LANG_GLOBAL_DIRECTION']}" lang="{$lang->items['LANG_GLOBAL_LANGCODE']}" xml:lang="{$lang->items['LANG_GLOBAL_LANGCODE']}">
<head>
<title>$master_board_name | {$lang->items['LANG_THREAD_POLL_USER_VOTET']}</title>
$headinclude
</head>
<body>
<table cellpadding="{$style['tableincellpadding']}" cellspacing="{$style['tableincellspacing']}" border="{$style['tableinborder']}" style="width:{$style['tableinwidth']}" class="tableinborder">
 <tr>
  <th class="tabletitle" style="white-space:nowrap">
   <span class="normalfont"><b>{$lang->items['LANG_THREAD_POLL_USER_VOTET']}</b></span>
  </th>
 </tr>
 <tr>
  <td class="tablea">
   <table cellpadding="{$style['tableincellpadding']}" cellspacing="{$style['tableincellspacing']}" border="{$style['tableinborder']}" style="width:100%; white-space:nowrap" class="tableinborder">
    <tr>
     <th class="tablecat">
      <span class="smallfont">
        <a href="thread.php?action=who_vote&amp;pollid=$pollid&amp;ordermode=<if($ordermode==1)><then>2{$SID_ARG_2ND}" title="{$lang->items['LANG_POLL_SORTDESC']}</then><else>1{$SID_ARG_2ND}" title="{$lang->items['LANG_POLL_SORTASC']}</else></if>">{$lang->items['LANG_POLL_USERNAME']} <img src="{$style['imagefolder']}/<if($ordermode==1)><then>sortdesc</then><else>sortasc</else></if>.gif" alt="" border="" /></a></span>
     </th>
     <if($wbbuserdata['a_can_view_whovote_detailed']!=0)><then>
      <th class="tablecat">
       <span class="smallfont">
        <a href="thread.php?action=who_vote&amp;pollid=$pollid&amp;ordermode=<if($ordermode==3)><then>4{$SID_ARG_2ND}" title="{$lang->items['LANG_POLL_SORTDESC']}</then><else>3{$SID_ARG_2ND}" title="{$lang->items['LANG_POLL_SORTASC']}</else></if>">{$lang->items['LANG_POLL_OPTION']} <img src="{$style['imagefolder']}/<if($ordermode==3)><then>sortdesc</then><else>sortasc</else></if>.gif" alt="" border="" /></a></span>
      </th>
     </then></if>
    </tr>
    $user_votet
   </table>
   <if($pagelink!='')><then><br />
   <span class="smallfont">$pagelink&nbsp;</span></then></if>
  </td>
 </tr>
 <tr>	
  <td class="tabletitle" align="center">
   <span class="smallfont"><a href="javascript:self.close();">{$lang->items['LANG_POLL_CLOSE_WINDOW']}</a></span>
  </td>
 </tr>
</table>
</body>
</html>