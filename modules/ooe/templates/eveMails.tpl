<link rel="stylesheet" type="text/css" href="modules/ooe/inc/evemail.css" />
<script type="text/javascript" src="modules/ooe/inc/evemail.js"></script>
<script type="text/javascript" src="classes/jqry_plugins/jquery.ba-dotimeout.min.js"></script>

{if $mails.error} 
	{foreach from=$mails.error item=error}<p>{$error}</p>{/foreach} 
{else}
Next update in: <span class="timer">{$offset}</span>
{foreach from=$mails item=group key=k}
{if $group[0]}
<table class="evemail" cellspacing="0" cellpadding="3">
  <thead>
  <tr>
    <td width="200" colspan="2">Datum</td>
    <td width="150">Name</td>
    <td width="350">Titel</td>
	<td width="100" align="right">{$k}</td>
  </tr>
  </thead>
  <tbody>
{foreach from=$group item=mail}
  <tr id="id_{$mail.messageID}_{$sel_char}" class="{cycle values="up,down"}">
    <td>{$mail.sentDate.day}</td>
	<td>{$mail.sentDate.time}</td>
    <td>{$mail.senderName}</td>
	{if $k=='List'}
    <td>{$mail.title}</td>
	<td align="right">{$mail.toListID}</td>
    {else}
	<td colspan="2">{$mail.title}</td>
	{/if}
  </tr>
{/foreach}
  </tbody>
</table>
{/if}
{/foreach}
{/if}
<div id="mail-box" class="mail-popup"></div>