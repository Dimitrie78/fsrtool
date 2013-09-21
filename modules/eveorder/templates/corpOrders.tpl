{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
<h4>{$language.eveorder_submenu}</h4>
<a href="{$url_index_eveorder}&amp;action=main">{$language.order}</a> - 
<a href="{$url_index_eveorder}&amp;action=myOrders">{$language.my_orders}</a> -
<a href="{$url_index_eveorder}&amp;action=myFavorites">{$language.my_favorites}</a> -
<a href="{$url_index_eveorder}&amp;action=corpOrders">Corp Orders</a>
{if ($openOrders)}<form action="{$url_index_eveorder}" method="get">
				{$language.show_orders_with_status}<select name="orderStatus">{foreach from=$status item=thisStatus}
					{if ((!(($thisStatus.value == "1") AND ($producer != "1"))) AND (!(($thisStatus.value == "-1") AND ($manager != "1"))) AND (!(($thisStatus.value == "2") AND ($supplier != "1"))))}
					<option value="{$thisStatus.value}"{if ($thisStatus.value == $whichStatus)} selected="selected"{/if}>{$thisStatus.name}</option>{/if}{/foreach}
				</select><input type="submit" value="{$language.send}">
				<input type="hidden" name="action" value="openOrders" />
				<input type="hidden" name="module" value="eveorder" />
			</form>
{/if}<br />
<br />
	<h3>{$language.my_orders_so_far}</h3>
	<table width="1100" cellspacing="0" cellpadding="4">
		<tr>
			<th width="100">
				User
			</th>
			<th width="250">
				Item
			</th>
			<th>
				{$language.Count}
			</th>
			<th>
				{$language.ort}
			</th>
			<th width="120">
				{$language.order_date}
			</th>
			<th width="100">
				Status
			</th>
			<th width="120">
				{$language.last_change}
			</th>
			<th width="120">
				{$language.last_processor}
			</th>
			<th>&nbsp;
				
			</th>
		</tr>
		{foreach from=$orders item=thisOrder}
		<tr bgcolor="{cycle values="#333333,#444444"}">
			<td>
				{if ($igb == "1") AND ($thisOrder.username != $curUser->uname)}
					{if ($thisOrder.username != "")}
					<a href="Javascript:CCPEVE.showInfo(1377,{$thisOrder.userid})">{$thisOrder.username}</a>
					{else} <b><font color="#FF0000">>>???<<</font></b> {/if}
				{else}
					{if ($thisOrder.username != "")} {$thisOrder.username} {else} <b><font color="#FF0000">>>???<<</font></b> {/if}
				{/if}
				
			</td>
			<td>
				{if ($igb == "1")}
					<a href="Javascript:CCPEVE.showInfo({$thisOrder.typeID})">{$thisOrder.typeName}</a>
				{else}
					{$thisOrder.typeName}
				{/if}
			</td>
			<td style="text-align:right;">
				{$thisOrder.amount}
			</td>
			<td style="text-align:center;">
				D7-ZAC
			</td>
			<td style="text-align:center;">
				{$thisOrder.timestamp|date_format:"%d.%m.%Y %H:%M"}
			</td>
			<td>
				{if ($thisOrder.status == "-1")}
					{assign var=orderStatus value=0}
				{else}
					{assign var=orderStatus value=$thisOrder.status}
				{/if}
				{if ($thisOrder.status == "-1")}
					<font color="#FF0000">{$status.$orderStatus.name}</font>
				{/if}
				{if ($thisOrder.status == "0")}
					<font color="#FF0000">{$status.$orderStatus.name}</font>
				{/if}
				{if ($thisOrder.status == "1")}
					<font color="#FF9900">{$status.$orderStatus.name}</font>
				{/if}
				{if ($thisOrder.status == "2")}
					<font color="#FF9900">{$status.$orderStatus.name}</font>
				{/if}
				{if ($thisOrder.status == "3")}
					<font color="#009900">{$status.$orderStatus.name}</font>
				{/if}
				{if ($thisOrder.status == "4")}
					<font color="#00FF00">{$status.$orderStatus.name}</font>
				{/if}				
				<br />{$thisOrder.comment}
			</td>
			<td style="text-align:center;">
				{$thisOrder.lastchange|date_format:"%d.%m.%Y %H:%M"}
			</td>
			<td>
				{$thisOrder.supplierName}
			</td>
			<td>
				{if (($thisOrder.username != "") AND ($thisOrder.status == "0" OR $thisOrder.status == "4") and ($manger or $supplier or $producer))}
				<a href="{$url_dowork_delOrder}&amp;orderID={$thisOrder.id}"><img title="{$language.delete}" src="icons/delete.png" /></a>
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}