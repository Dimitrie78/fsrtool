{include file="header.tpl"}
{include file="file:[Productions]menu.tpl"}


<div class="jbcontent">

	<div class="jbhead">Manage Characters</div><br/>
	
	{foreach from=$apis item=api}
	<form id="edit" action="" method="post">
	<table class="jbformtable">
		<tbody>
			<tr><td colspan="2">{$api.charName} : {$api.corpName}</td></tr>
			<tr>
				<td valign="middle"><img class="api{$api.status}" src="https://image.eveonline.com/Character/{$api.charID}_64.jpg" /></td>
				<td valign="top">
					KeyID:<br/>
					<input name="keyid" type="text" value="{$api.keyID}" /><br/>
					vCode:<br/>
					<input name="vcode" type="text" value="{$api.vCode}"  size="75" />
					<input name="charid" type="hidden" value="{$api.charID}" />
					<input id="save" name="save" type="submit" value="Save changes"/>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
	{/foreach}
	
	<div class="jbhead">Add Characters</div><br/>
	
	Adding a character requires your Corp <a href="https://support.eveonline.com/api/Key/Create" target="_blank">API Key</a> for every Corp you want to use. (Corp: Locations, Assetslist)
	<form class="jbform" id="addchar" action="" method="post">
		KeyID:<br/>
		<input id="keyid" name="keyid" type="text"/><br/>
		vCode:<br/>
		<input id="vcode" name="vcode" type="text" size="75"/>
		<input id="fetch" name="fetch" type="submit" value="Fetch Characters"/>
	</form>
	
	<div id="error"></div>

</div>


{include file="footer.tpl"}