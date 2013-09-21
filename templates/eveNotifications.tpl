{include file="header.tpl"}
<div id="title">&raquo; Notifications</div>
{include file="menu.tpl"}

<div class="page">
	<div id="cssmenu">
	<ul>
	   <li{if !$sub} class="active"{/if}><a href='{$url_index}?action=eveNotifications'><span>Home</span></a></li>
	   <li{if $sub && $sub=='options'} class="active"{/if}><a href='{$url_index}?action=eveNotifications&sub=options'><span>Options</span></a></li>
	</ul>
	</div>
{if !$sub}
	<div id="main">
{if $eveNotifications->error}
	{$eveNotifications->error}
{else}
	<table>
	  <tr class="head">
		<th>&nbsp;</th>
		<th>Received</th>
		<th>From</th>
		<th>Type</th>
	  </tr>
	{foreach $eveNotifications->Notifications as $Notifications}
	{cycle values='even,odd' assign=CellCSS}
	  <tr class="{$CellCSS}">
		<td class="icon">{if $Notifications.read}<img src="icons/email_open.png" title="old" alt="old" />{else}<img src="icons/email.png" title="new" alt="new" />{/if}</td>
		<td class="time"><span class="day">{$Notifications.sendTime.day}</span>{$Notifications.sendTime.time}</td>
		<td>{$Notifications.senderName}</td>
		<td>{$Notifications.typeName}</td>
	  </tr>
{*	  <tr class="nodeText">
		<td>&nbsp;</td>
		<td colspan="3">{$Notifications.nodeText}</td>
	  <tr> *}
	{/foreach}
	</table>
{/if}
	</div>
{else}	
	<div id="main">
	<p>Send <a href="https://pushover.net/" target="blank">Pushover</a> Notivications to:&nbsp;
	<img id="addPush" src="icons/cog_add.png" title="edit" alt="edit" />
	<span id="push">
	{if isset($eveNotifications->pushMail[0].push_user)}
	<br />
	User:&nbsp;{$eveNotifications->pushMail[0].push_user}<br />
	Token:&nbsp;{$eveNotifications->pushMail[0].push_token}<br />
	Status:&nbsp;{if $eveNotifications->pushMail[0].push_valid}<span class="ok">ok</span>{else}<span class="bad">bad</span>{/if}
	&nbsp;<img id="delPush" src="icons/delete.png" title="delete" alt="delete" />
	{/if}
	</span>
	</p>
	
	<p>Send E-Mail Notivications to:&nbsp;
	<img id="addMail" src="icons/cog_add.png" title="edit" alt="edit" />
	
	<span id="mail">
	{if isset($eveNotifications->pushMail[0].email)}<br />
	Status:&nbsp;{if $eveNotifications->pushMail[0].mail_valid}<span class="ok">ok</span>{else}<span class="bad">bad</span>{/if}
	<br />
		{$eveNotifications->pushMail[0].email}&nbsp;
		<img id="delMail" src="icons/delete.png" title="delete" alt="delete" />
	{/if}
	</span>
	</p>
	<table>
	  <tr class="head">
		<th>#</th>
		<th>Type</th>
		<th>-</th>
	  </tr>
	{if is_array($eveNotifications->pushMail[0].notivications)}
	{$noti = $eveNotifications->pushMail[0].notivications}
	{else}{$noti = array()}{/if}
	{foreach $eveNotifications->NotificationTypes as $NotificationTypes}
	{cycle values='even,odd' assign=CellCSS}
	  <tr class="{$CellCSS}">
		<td>{$NotificationTypes@key}</td>
		<td>{$NotificationTypes}</td>
		<td class="check"><input type="checkbox" value="{$NotificationTypes@key}" {if in_array($NotificationTypes@key, $noti)}checked="checked"{/if} /></td>
	  </tr>
	{/foreach}
	</table>
	</div>
{/if}
</div>

{include file="footer.tpl"}