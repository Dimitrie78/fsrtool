{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}{/if}
<br>
{if ($poslist)}
<table cellpadding="2" cellspacing="1" style="border:2px solid #000">
  <tr style="background-color:#4F0202">
    <td align="center">#</td>
	<td align="center">Icon</td>
	<td align="center"><a href="{$index}&action={$Status}&sort=typeID">Type</a></td>
	<td align="center"><a href="{$index}&action={$Status}&sort=regionName">Region</a></td>
	<td align="center"><a href="{$index}&action={$Status}&sort=Moon">Moon</a></td>
	<td align="center"><a href="{$index}&action={$Status}&sort=time"><u>State</u><br />maxTime</a></td>
	<td align="center"><a href="{$index}&action={$Status}&sort=manager">Manager</a></td>
	<td align="center">Fuel Blocks</td>
	<td align="center">Stront</td>
  </tr>
{foreach from=$poslist item=thisPos}
{if 	($thisPos.online <= '100')} {assign var="color" value="#FF0000"}
{elseif ($thisPos.online <= '240')} {assign var="color" value="#000080"}
{else} 								{assign var="color" value="#004000"} {/if}
{if		($thisPos.state == 'Online' or $thisPos.state == 'Offline')}
  <tr bgcolor="{$color}">
    <td>{counter}</td>
	<td>{* if $canEdit *}<a href="{$index}&action=editPos&id={$thisPos.posID}">{$thisPos.icon32}</a></td>
	<td align="center">{$thisPos.tower}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="center">{$thisPos.state}<br />{$thisPos.onlinetime}</td>
	<td align="center">{if ($thisPos.manager)}{$thisPos.manager}{else}&nbsp;{/if}</td>
	<td align="center">{$thisPos.Blocks}</td>
	<td align="center">{$thisPos.StrontiumCalthrates}</td>
  </tr>
{else}
  <tr bgcolor="#000000">
    <td>{counter}</td>
	<td>{* if $canEdit *}<a href="{$index}&action=editPos&id={$thisPos.posID}">{$thisPos.icon32}</a></td>
	<td align="center">{$thisPos.tower}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="center">{$thisPos.state}<br />{$thisPos.rftime}</td>
	<td align="center">{if ($thisPos.manager)}{$thisPos.manager}{else}&nbsp;{/if}</td>
	<td align="center">{$thisPos.stateTimestamp}</td>
	<td align="center">{$thisPos.StrontiumCalthrates}</td>
  </tr>
{/if}
{/foreach}
</table>
{else}
There are no {$Status} Towers
{/if}
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}