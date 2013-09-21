{* Smarty *}{debug}
{************ Pos.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

<script type="text/javascript" src="classes/jqry_plugins/jquery.ba-dotimeout.min.js"></script>
<script type="text/javascript" src="classes/jqry_plugins/timer.js"></script>

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('span.timer').each(function(){
		$(this).timer();
	});
	$('tr.merge').hover(function(){
		var prev = $(this).prev('.merge_group');
		prev.css('background-color',prev.css('background-color') == 'rgb(128, 0, 0)' ? 'none' : '#800000' );
		$(this).css('background-color',$(this).css('background-color') == 'rgb(128, 0, 0)' ? 'none' : '#800000' );
	});
	$('tr.merge_group').click(function(){
		$('tr.'+$(this).attr('id')).each(function(){
			$(this).css('display',($(this).css('display')=='none' ? '' : 'none'));
		});	
	});
	$('input#open_all').click(function(){
		$('tr.merge').css('display',$('input#open_all:checked').length == 0 ? 'none' : '');
	});
});
</script>{/literal}


<div id="title">&raquo; POS</div>
<div id="menu">
<ul class="items">
	<li {if $Status == "online"}id="selected"{/if}><a href="{$url_index_module}&module=Pos&action=online">online</a></li>
	<li {if $Status == "offline"}id="selected"{/if}><a href="{$url_index_module}&module=Pos&action=offline">offline</a></li>
	<li><a href="{$url_index_module}&module=Pos&action=phpBB">to phpBB</a></li>
	<li><a href="{$url_index_module}&module=Pos&action=settings">settings</a></li>
	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}&amp;module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>

{if ($corps)}
<div>
<form action="{$url_index_module}&module=Pos&action=editPos&action={$Status}" method="post">
  <select name="corpid" onchange="submit()">
  {html_options options=$corps selected=$sel_corp}
  </select>
</form>
</div>
{/if}
<div align="center">
<br>
{if ($poslist)}
<table class="data" cellpadding="3" cellspacing="0">
  <thead>
  <tr>
	<td colspan="5"><span class="head">POSen mit Status: <i>{$Status}</i> der Corp <i>{$corps[$sel_corp]}</i></span>
	<td colspan="3" style="text-align:right"><label for="open_all">Alle Gruppen &ouml;ffnen</label><input type="checkbox" id="open_all"/></span>
	</td>
  </tr>
  <tr class="headcol">
	<td width="32">Icon</td>
	<td><a href="{$url_index_module}&module=Pos&action={$Status}&sort=typeID">Type</a></td>
	<td align="center"><a href="{$url_index_module}&module=Pos&action={$Status}&sort=regionName">Region</a></td>
	<td><a href="{$url_index_module}&module=Pos&action={$Status}&sort=Moon">System</a></td>
	<td>Moon</td>
	<td align="center"><a href="{$url_index_module}&module=Pos&action={$Status}&sort=time">State</a></td>
	<td align="center"><a href="{$url_index_module}&module=Pos&action={$Status}&sort=manager">Manager</a></td>
	<td align="center"><a href="{$url_index_module}&module=Pos&action={$Status}&sort=time">maxTime</a></td>
  </tr></thead><tbody>
{foreach from=$poslist item=thisPos}
{cycle name=c values="bright,dark" assign=col}
{if 	($thisPos.online <= '100')}	{if $col == 'bright'}{assign var="color" value="#600000"}{else}{assign var="color" value="#400000"}{/if}
{elseif ($thisPos.online <= '240')} {if $col == 'bright'}{assign var="color" value="#005780"}{else}{assign var="color" value="#00344c"}{/if}
{else} 								{if $col == 'bright'}{assign var="color" value="#444444"}{else}{assign var="color" value="#333333"}{/if}
{/if}
{if		($thisPos.state == 'Online' or $thisPos.state == 'Offline')}
  <tr bgcolor="{$color}" class="merge_group" id="{$thisPos.posID}">
	<td><a href="{$url_index_module}&module=Pos&action=editPos&id={$thisPos.posID}" title="edit">{$thisPos.icon32}</a></td>
	<td>{$thisPos.tower|replace:"Control Tower":""}</td>
    <td align="center">{$thisPos.region}</td>
	<td>{$thisPos.sys}</td>
    <td>{$thisPos.moon}</td>
    <td align="center">{$thisPos.state}<br /></td>
	<td align="center">{if ($thisPos.manager)}{$thisPos.manager}{else}&nbsp;{/if}</td>
	<td align="center"><span class="timer">{$thisPos.time_online}</span></td>
  </tr>
{else}
{assign var="color" value="#000"}
  <tr bgcolor="{$color}" class="merge_group" onClick="togglePos('{$thisPos.posID}')">
	<td><a href="{$url_index_module}&module=Pos&action=editPos&id={$thisPos.posID}">{$thisPos.icon32}</a></td>
	<td align="center">{$thisPos.tower}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="center">{$thisPos.state}</td>
	<td align="center">{if ($thisPos.manager)}{$thisPos.manager}{else}&nbsp;{/if}</td>
	<td align="center" colspan="8">{$thisPos.stateTimestamp}</td>
	<td align="center">{$thisPos.StrontiumCalthrates}</td>
  </tr>
{/if}
  <tr bgcolor="{$color}" class="merge {$thisPos.posID}" style="display:none;">
	<td colspan="8" style="padding:0 5px 5px 5px;">
		<div style="background-color:#333;padding:2px">
		<table cellspacing="0" cellpadding="5" style="border:solid 3px #000;">
			<thead>
			<tr>
				<td>Typ</td>
				<td align="center">Vorhanden</td>
				<td align="center">Timer</td>
			</tr>
			</thead><tbody>
			<tr>
				<td>Uranium:</td>
				<td align="right">{$thisPos.towerfuel.Uranium|number_format}</td>
				<td><span class="timer">{$thisPos.time_Uranium}</span></td>
			</tr>
			<tr>
				<td>Oxygen:</td>
				<td align="right">{$thisPos.towerfuel.Oxygen|number_format}</td>
				<td><span class="timer">{$thisPos.time_Oxygen}</span></td>
			</tr>
			<tr>
				<td>Mechanical&nbsp;Parts:</td>
				<td align="right">{$thisPos.towerfuel.Mechanical|number_format}</td>
				<td><span class="timer">{$thisPos.time_MechanicalParts}</span></td>
			</tr>
			<tr>
				<td>Coolant:</td>
				<td align="right">{$thisPos.towerfuel.Coolant|number_format}</td>
				<td><span class="timer">{$thisPos.time_Coolant}</span></td>
			</tr>
			<tr>
				<td>Robotics:</td>
				<td align="right">{$thisPos.towerfuel.Robotics|number_format}</td>
				<td><span class="timer">{$thisPos.time_Robotics}</span></td>
			</tr>
			<tr>
				<td>Isotopes:</td>
				<td align="right">{$thisPos.towerfuel.Isotopes|number_format}</td>
				<td><span class="timer">{$thisPos.time_Isotopes}</span></td>
			</tr>
			<tr>
				<td>Liquid&nbsp;Ozone:</td>
				<td align="right">{$thisPos.towerfuel.LiquidOzone|number_format}</td>
				<td><span class="timer">{$thisPos.time_LiquidOzone}</span></td>
			</tr>
			<tr>
				<td>Heavy&nbsp;Water:</td>
				<td align="right">{$thisPos.towerfuel.HeavyWater|number_format}</td>
				<td><span class="timer">{$thisPos.time_HeavyWater}</span></td>
			</tr>
			<tr>
				<td>Strontium:</td>
				<td align="right">{$thisPos.towerfuel.Stront|number_format}</td>
				<td>{$thisPos.time_StrontiumCalthrates}</td>
			</tr></tbody>
		</table>
		</div>
	</td>
  </tr>
{/foreach}</tbody>
</table>
{else}
There are no {$Status} Towers
{/if}
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}