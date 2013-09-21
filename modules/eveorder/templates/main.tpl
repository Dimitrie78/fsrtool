{include file="header.tpl"}
{include file="file:[eveorder]menu.tpl"}

<table style="border:none;" width="900">
	<tr>
		<td valign="top" width="300">
		<table cellpadding="0" cellspacing="0" border="0">
		
{foreach from=$market item=thisGroup}
		<tr>
		<td align="left">
	{if ($thisGroup.ebene > 0)}&nbsp;&nbsp;{/if}{if ($thisGroup.ebene > 1)}&nbsp;&nbsp;{/if}{if ($thisGroup.ebene > 2)}&nbsp;&nbsp;{/if}{if ($thisGroup.ebene > 3)}&nbsp;&nbsp;{/if}{if ($thisGroup.ebene > 4)}&nbsp;&nbsp;{/if}
	{if ($thisGroup.ebene > 0)}
	<img src="{$thisGroup.icon}" width="18" height="18" alt="icon"><a href="{$url_index_main}&amp;open={$thisGroup.open}">{$thisGroup.marketGroupName}</a><br />
	{else}
	<img src="{$thisGroup.icon}" width="18" height="18" alt="icon"><a href="{$url_index_main}&amp;open={$thisGroup.altopen}">{$thisGroup.marketGroupName}</a><br />
	{/if}
		</td></tr>
{/foreach}
		
		<tr><td>
<br />
{literal}<script type="text/javascript">
$(document).ready(function(){
	$('#search').autocomplete({
		minLength: 3,
		source: "dowork.php?module=eveorder&action=ajaxSearch"
	});
});
</script>{/literal}

<form action="{$url_dowork_search}" method="post">
			<input type="hidden" name="action" value="search" />
			<input type="hidden" name="module" value="eveorder" />
			<input type="text" id="search" name="search" size="35"/><input type="submit" value="{$language.Search}" />
		</form>
		</td></tr>
		</table>
		
</td>
<td valign="top" width="600">
	<table style="text-align:left">
	{if isset($items)}{assign var="item" value=1}
<form action="{$url_dowork_saveOrder}" method="post" id="order"">
{foreach from=$items item=thisType}
	<tr>
		<td width="64" height="64" valign="top">{$thisType.iconOGB}</td>
		<td>
			<h4 style="text-align:left">{$thisType.typeName}</h4><br/>
			{if ($igb)}
				<img src="icons/icons/16_16/icon38_208.png" width="16" height="16" alt="info" onclick="CCPEVE.showInfo({$thisType.typeID})">
				{if $items.0.categoryID == 6 || $items.0.categoryID == 18}<img src="icons/preview.png" width="16" height="16" alt="preview" onclick="CCPEVE.showPreview({$thisType.typeID})">{/if}
			{/if}
		</td>
	</tr>
	<tr><td colspan="2">{$thisType.description}<br />Eve-Central-{$language.Price} ({$thisType.fetched|date_format:"%d.%m.%Y %H:%M"}): {$thisType.price|number_format:2:',':'.'}</td></tr>
	<tr><td colspan="2" align="right">
		<a href="{$url_dowork_addToFavorites}&amp;open={$open}&amp;groupID={$thisType.groupID}&amp;typeID={$thisType.typeID}">{$language.add_favorites}</a>
	<input type="hidden" name="typeID[]" value="{$thisType.typeID}" />
{if ($corporder or $manager)}F&uuml;r Corp:<input type="checkbox" name="corp[{$thisType.typeID}]" value="1" />{/if}
	{$language.Count}: <input type="text" class="amount" name="amount[]" size="6" maxlength="10" value="" />
	</td></tr>
	<tr><td colspan="2"><hr /></td></tr>
{/foreach}
	<tr><td colspan="2" align="center">
	<input type="hidden" name="action" value="saveOrder" />
	<input type="hidden" name="module" value="eveorder" />
	<input type="hidden" name="open" value="{$open}" />
	<input type="submit" value="{$language.order_item}" />
</form></td></tr>
	{else}
	<tr>
		<td colspan="2">
			<h2>{$language.fitting_order}</h2>
		</td>
	</tr>
	
{literal}<script type="text/javascript">
$(document).ready(function(){
	$('input:radio').click(function(){
		if($(this).attr('value')=='xml'){
			$('textarea#fitting').css('display','none');
			$('input#xmlupload').css('display','inline');
		}else{
			$('textarea#fitting').css('display','inline');
			$('input#xmlupload').css('display','none');
		}
	});
});
</script>{/literal}

	<form enctype="multipart/form-data" action="{$url_dowork_saveOrder}" method="post" id="order">
	<tr>
		<td rowspan="2" style="vertical-align:top">
			<textarea id="fitting" name="fitting" cols="35" rows="20"></textarea>
			<input id="xmlupload" type="file" name="xml" style="display:none"/>
		</td><td style="vertical-align:top">
			<input id="type_eft" type="radio" name="type" value="eft" checked="checked"/><label for="type_eft">EFT Fitting</label><br/>
			{if ($igb)}<input id="type_igf" type="radio" name="type" value="igf" /><label for="type_igf">InGame Format<br/>({$language.eve_message})</label>
			{else}<input id="type_xml" type="radio" name="type" value="xml" /><label for="type_xml">XML Fitting</label>{/if}
		</td>
	</tr>
	<tr>
		<td style="vertical-align:bottom">
			{$language.Count}: <input type="text" class="amount" name="amount" size="2" maxlength="3" value="1" /><br/>
			{if ($corporder or $manager)}<input type="checkbox" name="corp" value="1" />for Corp<br/>{/if}
			<input type="hidden" name="action" value="saveFittingOrder" />
			<input type="hidden" name="open" value="{$open}" />
			<input type="submit" value="{$language.order_fitting}" />
		</td>
	</tr>
	</form>
	{/if}
	</table>
</td>
</tr>
</table>

{include file="footer.tpl"}