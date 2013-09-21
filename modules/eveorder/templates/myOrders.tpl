{include file="header.tpl"}    
{include file="file:[eveorder]menu.tpl"}
	
	<table class="data" cellspacing="0" cellpadding="3">
		<thead>
		<tr>
			<td colspan="{if $orders}4{else}9{/if}" style="padding-bottom:2px;"><span class="head">{$language.my_orders_so_far}</span> ({if $orders|@count > 1}{$orders|@count} {$language.orderss}{elseif $orders|@count == 1}{$language.an_order}{else}{$language.no_orders_available}{/if})
			</td>
			{if $orders}<td colspan="5" style="text-align:right">
				<form action="{$url_dowork_eveorder}" method="post">
					<input type="hidden" name="action" value="delallDeliverys">
					{$language.delall}
					<select name="delall">
						<option value="4">{$status[4].name}</option>
						<option value="-1">{assign var="minusone" value="-1"}{$status[$minusone].name}</option>
						<option value="0">{$status[0].name}</option>
					</select>
					<input type="submit" value="{$language.delete}">
				</form>
			</td>{/if}
		</tr>
		{if $orders}
		<tr class="headcol">
			<td width="250">Item</td>
			<td width="50">{$language.Count}</td>
			<td width="80">{$language.order_date}</td>
			<td width="80">Status</td>
			{*<td width="100">Target</td>*}
			<td width="100">Price</td>
			<td>Kommentar</td>
			<td width="80">{$language.last_change}</td>
			<td width="120">{$language.last_processor}</td>
			<td width="20">&nbsp;</td>
		</tr>
		</thead><tbody>
		<form action="{$dowork}" method="post">
		{$endsum = 1}
		{foreach from=$orders item=thisOrder}
		<tr bgcolor="{cycle values="#333333,#444444"}">
			<td style="font-size:9px">
				{if $thisOrder.corpID}
					<span style="color:#FF0000">Corp:</span>
				{/if}
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
				{$thisOrder.timestamp|date_format:"%d.%m.%Y"}
			</td>
			<td>
				{assign var=orderStatus value=$thisOrder.status}<span style="color:{if $thisOrder.status == "-1" || $thisOrder.status == "0"}#FF0000{elseif $thisOrder.status == "1" || $thisOrder.status == "2"}#FF9900{elseif $thisOrder.status == "3" || $thisOrder.status == "4"}#00FF00{/if}">{$status.$orderStatus.name}</span>		
			</td>
			{*<td>{assign var=target value=$thisOrder.targetSys}
				{if ($thisOrder.status == "0")}
				{html_options name=target[] options=$targetSystems selected=$target}
				{else}
				{$targetSystems.$target}
				{/if}</td>*}
			
			{if $thisOrder.status != "4"}
			{if $thisOrder.price}
			{math equation="x * y" x=$thisOrder.price y=$thisOrder.amount assign=sum}
			{else}{assign var="sum" value="0"}{/if}
			{assign var="endsum" value="`$endsum+$sum`"}
			
			<td align="right">{$sum|number_format:2:',':'.'}</td>
			{else}
			{if $thisOrder.price}
			{math equation="x * y" x=$thisOrder.price y=$thisOrder.amount assign=sums}
			{else}{assign var="sums" value="0"}{/if}
			<td align="right">{$sums|number_format:2:',':'.'}</td>{/if}
			
			<td>{if ($thisOrder.status == "0")}
				<input type="text" name="comment[]" value="{$thisOrder.comment}" size="35" maxlength="50" />
				<input type="hidden" name="id[]" value="{$thisOrder.id}" />
				<input type="hidden" name="status[]" value="{$orderStatus}" />
				<input type="hidden" name="check[{$thisOrder.id}]" value="1" />
				{else}
				{if $thisOrder.comment}{$thisOrder.comment}{else}-{/if}
				{/if}</td>
			<td style="text-align:center;">
				{$thisOrder.lastchange|date_format:"%d.%m.%Y"}
			</td>
			<td>
				{$thisOrder.supplierName}
			</td>
			<td>
				{if ($thisOrder.status == "0" OR $thisOrder.status == "4" OR $thisOrder.status == "-1")}
				<a href="{$url_dowork_delOrder}&amp;orderID={$thisOrder.id}"><img title="{$language.delete}" src="icons/delete.png" /></a>
				{/if}
			</td>
		</tr>
		{/foreach}
		<tr>
			<td colspan="9" align="center"><hr></td>
		</tr>
		<tr>
			<td colspan="9" align="center">Total amount: {$endsum|number_format:2:',':'.'}</td>
		</tr>
		<tr>
			<td colspan="9" align="center"><input type="submit" value="Go" /></td>
		</tr>
		<input type="hidden" name="module" value="eveorder" />
		<input type="hidden" name="action" value="changeMyOrderStatus" />
		</form>
		{else}
		</thead><tbody><tr bgcolor="#333333">
			<td colspan="9">{$language.orders}</td>
		</tr>{/if}
		</tbody>
	</table>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}