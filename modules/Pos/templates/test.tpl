{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

{if !$ApiStatus}<input type="button" value="Fetch API" name="update" id="update" />{/if}
<div id="result"></div>
<div class="jbcontent">
	{if $apis}
	<div class="jbhead">Manage Character</div><br/>
	
	<form id="savechar" action="" method="post">
	<table class="jbformtable">
		<tbody>
			<tr><td colspan="2">{$apis.UserName} : {$apis.CorpName}</td></tr>
			<tr>
				<td valign="middle"><img class="api{$apis.status}" src="https://image.eveonline.com/Character/{$apis.CharID}_64.jpg" /></td>
				<td valign="top">
					KeyID:<br/>
					<input id="keyid" name="keyid" type="text" value="{$apis.keyID}" /><br/>
					vCode:<br/>
					<input id="vcode" name="vcode" type="text" value="{$apis.vCODE}"  size="80" />
					<input id="charid" name="charid" type="hidden" value="{$apis.CharID}" />
					<input id="corpid" name="corpid" type="hidden" value="{$apis.CorpID}" />
					<input id="fetch" name="fetch" type="submit" value="Save changes"/>
					<input id="delall" name="delall" type="button" value="Delete all"/>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
	
	<div class="jbhead">Email Notivications options</div><br/>
	
	<table class="jbformtable">
		<tr><td colspan="2">Send low Fuel Email when Fuel lower than <input id="time" name="time" type="text" value="{$apis.lowftime}" size="3" /> hours</td></tr>
		{foreach from=$emails item=mail}
		  <tr><td>{$mail.email}</td><td><img src="icons/delete.png" class="del" alt="del" title="del" /></td></td></tr>
		{/foreach}
		<tr><td colspan="2"> &nbsp; </td></tr>
		<tr><td colspan="2"><input id="email" name="email" type="text" size="50" /> <input id="addmail" name="addmail" value="add" type="button" /></td></tr>
		
	</table>
	
	<div class="jbhead">API Error logs</div><br/>
	{if ($log)}
	<table class="jbformtable">
	{foreach from=$log item=thisLog}
		<tr>
			<td> {$thisLog.logtime} </td>
			<td> {$thisLog.code} </td>
			<td> {$thisLog.message} </td>
		</tr>
	{/foreach}
	</table>
	{/if}
	{else}
	
	<div class="jbhead">Add Character</div><br/>
	
	Adding a character requires your Corp <a href="https://support.eveonline.com/api/Key/Create" target="_blank">API Key</a>. 
	(WalletJournal, MemberTrackingExtended, StarbaseList, StarbaseDetail, Locations, CorporationSheet, AssetList)
	<form class="jbform" id="addchar" action="" method="post">
		KeyID:<br/>
		<input id="keyid" name="keyid" type="text"/><br/>
		vCode:<br/>
		<input id="vcode" name="vcode" type="text" size="80"/>
		<input id="fetch" name="fetch" type="submit" value="Fetch Characters"/>
	</form>
	{/if}
	
	
	<div id="error"></div>
	
</div>

{include file="footer.tpl"}