{* Smarty *}
{************ makeNotice.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; MiningMax - Notification</div>
<div id="menu">
<ul class="items">
	<li id="selected">Confirm</a></li>
	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

<table align="center" cellpadding="2" cellspacing="0" border=0>
 <tr bgcolor="#555555">
  <td rowspan="1" colspan="2"><b>{$IMG}</b></td>
 </tr>
 <tr bgcolor="#333333" >
  <td rowspan="1" colspan="2">{$CONFIRM}</td>
 </tr>
 <tr bgcolor="#555555">
  <td rowspan="1" colspan="2"><br>{$CONFIRMTEXT}<br><br></td>
 </tr>
 <tr bgcolor="#333333" >
  <td rowspan="1" colspan="1" align="left">{$CANCEL}</td>
  <td rowspan="1" colspan="1" align="right">{$OK}</td>
 </tr>
</table>
<br /><br />

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}