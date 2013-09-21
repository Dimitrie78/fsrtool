{* Smarty *}
{************ makeNotice.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; MiningMax - Notification</div>
<div id="menu">
<ul class="items">
	<li id="selected">{$WHAT}</a></li>
	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

<table align="center" border="0" cellspacing="0" cellpadding="1">
 <tr>
  <td bgcolor="#555555" width="35"> <img src="{$IMG}"></td>
  <td bgcolor="#555555" valign="bottom"><b>{$TITLE}</b></td>
 </tr>
 <tr>
  <td bgcolor="333333" colspan="2"><br>{$BODY}<br><br></td>
 </tr>
 <tr>
  <td bgcolor="#444444" align="center" colspan="2"><a href="{$BACKLINK}">{$BACKLINKDESC}</a></td>
 </tr>
 <tr>
  <td align="right" bgcolor="#333333" colspan="2"><i>{$USER}<br />{$WHAT} um {$TIME}.</i></td>
 </tr>
</table>
<br /><br />

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}