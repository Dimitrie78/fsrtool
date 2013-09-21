{include file="header.tpl"}
{include file="file:[eveorder]menu.tpl"}
<script type="text/javascript" src="modules/eveorder/inc/openOrders.js"></script>

	<table class="data" width="1000" cellspacing="0" cellpadding="3">
		<thead>
		<tr>
			<td colspan="6"><span class="head">{$language.open_orders} <i>&bdquo;{if ($whichStatus)}{$status[$whichStatus].name}{else}{$status[0].name}{/if}&rdquo;</i></span><br/> ({if ($quantity_orders)}{if ($quantity_orders > 1)}{$quantity_orders} {$language.orders_in} {if ($orders|@count) == 1}{$language.one_group}{else}{$orders|@count} {$language.groups}{/if}{else}{$language.an_order}{/if}{else}{$language.no_orders_available}{/if})</td>
			<td colspan="2" align="right">	
				{if ($openOrders)}<form action="{$url_index_eveorder}" method="get">
					{$language.show_orders_with_status}
					<select name="orderStatus" onChange="submit()">
						{foreach from=$status item=thisStatus}{if ((!(($thisStatus.value == "1") AND ($producer != "1"))) AND (!(($thisStatus.value == "-1") AND ($manager != "1"))) AND (!(($thisStatus.value == "2") AND ($supplier != "1"))))}<option value="{$thisStatus.value}" {if ($thisStatus.value == $whichStatus)} selected="selected"{/if}>{$thisStatus.name}</option>
						{/if}{/foreach}
						</select>
					<input type="hidden" name="action" value="openOrders" />
					<input type="hidden" name="module" value="eveorder" />
                    <input type="hidden" name="corpid" value="{$sel_corp}" />
				</form>{/if}				
			</td>
		</tr>
		<tr>
			<td colspan="8"> 
				<form id="settings" action="{$url_index_eveorder}" method="post">
					<div style="float:left;">{$language.show}:
						<input type="radio" name="for" value="all" {if ($for=='all' OR !$for)}checked="checked"{/if}/><label for="all">{$language.all_orders}</label>
						<input type="radio" name="for" value="user" {if ($for=='user')}checked="checked"{/if}/><label for="t1">User</label>
						<input type="radio" name="for" value="corp" {if ($for=='corp')}checked="checked"{/if}/><label for="gt1">Corp</label>
						<br/>total volume:&nbsp;{$volume|number_format:2:',':'.'}&nbsp;m&sup3;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;total ISK:&nbsp;{$price|number_format:2:',':'.'}&nbsp;ISK
					</div>
					<div style="text-align:right;">{$language.the_following_modules_ads}:
						<input type="radio" name="type" value="all" {if ($type=='all' OR !$type)}checked="checked"{/if}/><label for="all">Alle</label>
						<input type="radio" name="type" value="t1" {if ($type=='t1')}checked="checked"{/if}/><label for="t1">T1</label>
						<input type="radio" name="type" value="gt1" {if ($type=='gt1')}checked="checked"{/if}/><label for="gt1">&gt; T1</label>
						<input type="hidden" name="orderStatus" value="{if ($whichStatus)}{$whichStatus}{else}0{/if}" />
						{if ($sort)}<input type="hidden" name="orderby" value="{$sort}" />{/if}
						<input type="hidden" name="action" value="openOrders" />
						<input type="hidden" name="module" value="eveorder" />
                        <input type="hidden" name="corpid" value="{$sel_corp}" />
						<br/>
						<label for="open_all">{$language.all_groups_show}</label><input type="checkbox" id="open_all"/>
					</div>
				</form>
			</td>
		</tr>	
{if ($orders)}
		{if ($whichStatus == "4" or $whichStatus == "-1")}<tr>
			<td colspan="8">
				{$language.orders_with_the_status} <i>&bdquo;{$status[$whichStatus].name}&rdquo;</i> {$language.be_one_month_after_the_last_change_deleted}
			</td>
		</tr>{/if}
		<tr class="headcol">
			<td align="center" width="30">F&uuml;r</td>
			<td width="120"><a href="{$url_index_eveorder}&orderStatus={$orderStatus}&action=openOrders{if ($type)}&type={$type}{/if}&orderby=username">User</a></td>
			<td width="250"><a href="{$url_index_eveorder}&orderStatus={$orderStatus}&action=openOrders{if ($type)}&type={$type}{/if}&orderby=typeID">Item</a></td>
			<td width="50">{$language.Count}</td>
			<td align="center" width="80"><a href="{$url_index_eveorder}&orderStatus={$orderStatus}&action=openOrders{if ($type)}&type={$type}{/if}&orderby=date">{$language.order_date}</a></td>
			<td>Target</td>
			<td>Status</td>
			<td width="120">{$language.last_processor}</td>
		</tr>
		</thead><tbody>
	<form action="{$url_dowork_changeOrderStatus}" method="post">
{foreach from=$orders item=thisOrder key=thisKey}
{cycle name=gc values="light_red,medium_red" assign=gcol}
{if ($thisOrder.order|@count > 1)}
		<tr class="merge_group {$gcol}" onClick="toggleOrders('{$thisKey}')">
			<td align="center">&nbsp;</td>
			<td>{if ($igb == "1") AND ($thisOrder.username != $curUser->uname)}<a href="Javascript:CCPEVE.showInfo(1377,{$thisKey})">{$thisOrder.username}</a>{else}{$thisOrder.username}{/if}</td>
			<td>{$thisOrder.order|@count} {$language.orders_grouped}</td>
			<td align="left" colspan="3">shipVol:&nbsp;{$thisOrder.shipvol|number_format:2:',':'.'}&nbsp;m&sup3;</td>
			<td align="left" colspan="2">moduleVol:&nbsp;{$thisOrder.modvol|number_format:2:',':'.'}&nbsp;m&sup3; <span style="float:right;">{$thisOrder.price|number_format:2:',':'.'}&nbsp;ISK</span></td>
		</tr>
		<tr class="{$thisKey} merge {$gcol}" style="display:none;">
			<td colspan="8"  style="padding:0 5px 5px 5px;"><table cellspacing="0" cellpadding="3" border="0" style="width:100%;table-layout:fixed">
				<tr bgcolor="#000">
				  <td width="25">&nbsp;</td>
				  <td width="120">&nbsp;</td>
				  <td width="250">&nbsp;</td>
				  <td width="50">&nbsp;</td>
				  <td width="80">&nbsp;</td>
				  <td width="60">&nbsp;</td>
				  <td style="text-align:center;"><input type="button" value="All orders?" onClick="checkOrders('{$thisKey}')"/></td>
				  <td width="115">&nbsp;</td>
				</tr>
{foreach from=$thisOrder.order item=order}
{cycle name=c values="#333333,#444444" assign=col}
{assign var=ordertarget value=$order.targetSys}
				<tr bgcolor="{$col}">
					<td width="25">{if ($order.corpID != "")}<span style="color:#FF0000">Corp</span>{else}<span style="color:#00FF00">User</span>{/if}</td>
					<td width="120">{if ($igb == "1") AND ($thisOrder.username != $curUser->uname)}<a href="Javascript:CCPEVE.showInfo(1377,{$thisKey})">{$thisOrder.username}</a>{else}{$thisOrder.username}{/if}</td>
					<td width="250" style="font-size:9px">{if ($igb == "1")}<a href="Javascript:CCPEVE.showMarketDetails({$order.typeID})">{$order.typeName}</a>{else}{$order.typeName}{/if}</td>
					<td width="50" style="text-align:right;">{$order.amount}</td>
					<td width="80" style="text-align:center;">{$order.timestamp|date_format:"%d.%m.%Y"}</td>
					<td width="60" style="font-size:10px">{$targetSystems.$ordertarget}</td>
					<td>{if (($producer == "1") or ($supplier == "1") or ($manager == "1"))}
						<input id="check_{$thisKey}" type="checkbox" name="check[{$order.id}]" value="1" />
						<input type="text" name="comment[]" value="{$order.comment}" size="28" maxlength="255" />
						<input type="hidden" name="id[]" value="{$order.id}" />
                        <input type="hidden" name="target[]" value="{$order.targetSys}" />								
						{else}{assign var="orderStatus" value=$order.status}{$status[$orderStatus].name}<br />{$thisOrder[0].comment}
					{/if}</td>
					<td width="115">{if $order.supplierName}{$order.supplierName}{else}unbearbeitet{/if}</td>
				</tr>
{/foreach}		
			</table></td>
		</tr>
{else}
{assign var=target value=$thisOrder.order[0].targetSys}
		<tr bgcolor="{if ($gcol=='light_red')}#444444{else}#333333{/if}" class="{$thisKey}">
			<td align="center">{if ($thisOrder.order[0].corpID != "")}<span style="color:#FF0000">Corp</span>{else}<span style="color:#00FF00">User</span>{/if}</td>
			<td>{if ($igb == "1") AND ($thisOrder.username != $curUser->uname)}<a href="Javascript:CCPEVE.showInfo(1377,{$thisKey})">{$thisOrder.username}</a>{else}{$thisOrder.username}{/if}</td>
			<td style="font-size:9px">{if ($igb == "1")}<a href="Javascript:CCPEVE.showMarketDetails({$thisOrder.order[0].typeID})">{$thisOrder.order[0].typeName}</a>{else}{$thisOrder.order[0].typeName}{/if}</td>
			<td style="text-align:right;">{$thisOrder.order[0].amount}</td>
			<td style="text-align:center;">{$thisOrder.order[0].timestamp|date_format:"%d.%m.%Y"}</td>
			<td width="60" style="font-size:10px">{$targetSystems.$target}</td>
			<td>		
{if (($producer == "1") or ($supplier == "1") or ($manager == "1"))}
				<input type="checkbox" name="check[{$thisOrder.order[0].id}]" value="1" />
				<input type="text" name="comment[]" value="{$thisOrder.order[0].comment}" size="28" maxlength="255" />
				<input type="hidden" name="id[]" value="{$thisOrder.order[0].id}" />
                <input type="hidden" name="target[]" value="{$thisOrder.order[0].targetSys}" />	
{else}
{assign var="orderStatus" value=$thisOrder.order[0].status}
{$status[$orderStatus].name}<br />{$thisOrder[0].comment}
{/if}</td>
			<td>{if $thisOrder.order[0].supplierName}{$thisOrder.order[0].supplierName}{else}{$language.untreated}{/if}</td>
		</tr>
{/if}

{/foreach}

		</tbody><tfoot>
		<tr>
		  <td colspan="8" style="text-align:right;padding-top:5px;">
			{$language.status_of_selected_messages_to_change}: 
			<input type="hidden" name="action" value="changeOrderStatus" />
		<input type="hidden" name="module" value="eveorder" />
		<input type="hidden" name="orderStatus" value="{$whichStatus}" />
	<select name="status">
{foreach from=$status item=thisStatus}
{if ((!(($thisStatus.value == "1") AND ($producer != "1"))) AND (!(($thisStatus.value == "-1") AND ($manager != "1"))) AND (!(($thisStatus.value == "2") AND ($supplier != "1"))))}
		<option value="{$thisStatus.value}" {if ($whichStatus == $thisStatus.value)} selected{/if}>{$thisStatus.name}</option>
{/if}
{/foreach}
	</select>
    <input type="hidden" name="corpid" value="{$sel_corp}" />	
	<input type="submit" value="Go" />
	</form></td>
	</tr>
		</tfoot>
		
{else}

		</thead><tbody><tr bgcolor="#333333">
			<td colspan="8" style="text-align:center">{$language.orders}</td>
		</tr></tbody>

{/if}
		
		
	</table>

{include file="footer.tpl"}    