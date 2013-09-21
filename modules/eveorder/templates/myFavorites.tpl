{include file="header.tpl"}    
{include file="file:[eveorder]menu.tpl"}
	
	<table width="1000">
	{if ($items)}
<form action="{$url_dowork_saveOrder}" method="post">
{foreach from=$items item=thisType}
	<tr>
		<td width="64" height="64" valign="top">
			{$thisType.iconOGB}		
		</td>
		<td>
			<h4>{$thisType.typeName}</h4>
		</td>
		<td>
			Eve-Central-{$language.Price} ({$thisType.fetched|date_format:"%d.%m.%Y %H:%M"}): {$thisType.price|number_format:2:',':'.'}
		</td>
		<td align="right">
		<a href="{$url_dowork_delFromMyFavorites}&amp;typeID={$thisType.typeID}">{$language.del_favorites}</a>				
		<input type="hidden" name="typeID[]" value="{$thisType.typeID}" />
		{$language.Count}: <input type="text" class="amount" name="amount[]" size="6" maxlength="10" value="" /><br />
{if ($corporder or $manager)}F&uuml;r Corp:<input type="checkbox" name="corp[{$thisType.typeID}]" value="1" /><br />{/if}
		</td>
	</tr>
	<tr><td colspan="4"><hr /></td></tr>
{/foreach}
	<tr><td colspan="4" align="center">
	<input type="hidden" name="action" value="saveOrderFav" />
	<input type="hidden" name="module" value="eveorder" />
	<input type="submit" value="{$language.order_item}" />
</form></td></tr>
	{else}
		<tr><td>keine Favoriten vorhanden</td></tr>
	{/if}
	</table>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}
