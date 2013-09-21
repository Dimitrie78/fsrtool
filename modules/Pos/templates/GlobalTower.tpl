{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}
<br/>

<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}{/if}
<br>
{if ($poslist)}
<table cellpadding="2" cellspacing="1" style="border:2px solid #000; width:600px">
  <tr style="background-color:#4F0202">
    <td align="center" width="20">#</td>
	<td align="center" width="32">Icon</td>
	<td align="center">{if $curUser->Admin || $curUser->PosManager || $curUser->posalt}<a href="{$index}&action={$Status}&sort=typeID">{else}<a href="{$index}&sort=typeID">{/if}Type</a></td>
	<td align="center">{if $curUser->Admin || $curUser->PosManager || $curUser->posalt}<a href="{$index}&action={$Status}&sort=regionName">{else}<a href="{$index}&sort=regionName">{/if}Region</a></td>
	<td align="center">{if $curUser->Admin || $curUser->PosManager || $curUser->posalt}<a href="{$index}&action={$Status}&sort=Moon">{else}<a href="{$index}&sort=Moon">{/if}Moon</a></td>
    <td align="center" width="33">SMA</td>
    <td align="center" width="33">CHA</td>
    <td align="center" width="33">JB</td>
  </tr>
{foreach from=$poslist item=thisPos}
{assign var="color" value="#004000"}
{if		($thisPos.state == 'Online' or $thisPos.state == 'Offline')}
  <tr bgcolor="{$color}">
    <td>{counter}</td>
	<td>{if $canEdit}<a href="{$index}&action=editPos&id={$thisPos.posID}">{$thisPos.icon32}</a>{else}{$thisPos.icon32}{/if}</td>
	<td align="center">{$thisPos.tower}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="center" width="33">{if $thisPos.sma}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
    <td align="center" width="33">{if $thisPos.cha}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
    <td align="center" width="33">{if $thisPos.jb}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
  </tr>
{else}
  <tr bgcolor="#000000">
    <td>{counter}</td>
	<td>{if $canEdit}<a href="{$index}&action=editPos&id={$thisPos.posID}">{$thisPos.icon32}</a>{else}{$thisPos.icon32}{/if}</td>
	<td align="center">{$thisPos.tower}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="center" width="33">{if $thisPos.sma}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
    <td align="center" width="33">{if $thisPos.cha}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
    <td align="center" width="33">{if $thisPos.jb}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
  </tr>
{/if}
{/foreach}
</table>
{else}
There are no Towers for you
{/if}
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}