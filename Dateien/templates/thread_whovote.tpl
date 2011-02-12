<?xml version="1.0" encoding="{$lang->items['LANG_GLOBAL_ENCODING']}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{$lang->items['LANG_GLOBAL_DIRECTION']}" lang="{$lang->items['LANG_GLOBAL_LANGCODE']}" xml:lang="{$lang->items['LANG_GLOBAL_LANGCODE']}">
<head>
<title>$master_board_name | {$lang->items['LANG_THREADSRATING_1']}</title>
$headinclude
</head>
<body>
<table cellpadding="{$style['tableincellpadding']}" cellspacing="{$style['tableincellspacing']}" border="{$style['tableinborder']}" style="width:{$style['tableinwidth']}" class="tableinborder">
 <tr>
  <td class="tabletitle normalfont" style="width:100%" align="center"><b>{$lang->items['LANG_THREAD_POLL_USER_VOTET']}</b></td>
 </tr>
 <tr>
  <td class="tablea normalfont" align="center"><div style="width:100%; height: 250px; overflow: auto;">$user_votet</div></td>
 </tr>
 <tr>	
  <td class="tabletitle normalfont" align="center"><a href="javascript:self.close();">{$lang->items['LANG_POLL_CLOSE_WINDOW']}</a></td>
 </tr>
</table>
</body>
</html>