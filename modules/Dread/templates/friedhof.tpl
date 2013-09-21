{* Smarty *}
{************ Dread.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; Dreadtool</div>
<div id="menu">
<ul class="items">
	<li><a href="{$url_index}?module=Dread&action=main">{$language.overview}</a></li>
	<li><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li id="selected"><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

<div align="center">
<table class="data" cellpadding="3" cellspacing="0">
 <thead>
 <tr>
  <td colspan="9"><span class="head">{$language.elephant_graveyard} {$language.of} {$curUser->corpname}</span> {if $ships}({$ships|@count} {$language.pieces}){/if}</td>
 </tr>
 {if $ships} 
 <tr class="headcol">
   <td align="center"><a href="{$url_index}?module=Dread&action=tot&sort=id">#</a></td>
   <td><a href="{$url_index}?module=Dread&action=tot&sort=typ">Typ</a></td>
   <td><a href="{$url_index}?module=Dread&action=tot&sort=name">Name</a></td>
   <td align="center"><a href="{$url_index}?module=Dread&action=tot&sort=status">Status</a></td>
   <td align="center"><a href="{$url_index}?module=Dread&action=tot&sort=player">{$language.flown_by}</a></td>
   <td align="center"><a href="{$url_index}?module=Dread&action=tot&sort=time">{$language.flown_on}</a></td>
   <td align="center"><a href="{$url_index}?module=Dread&action=tot&sort=bemerkung">{$language.comment}</a></td>
   <td align="center">KBLink</td>
   <td align="center">*</td>
  </tr></thead><tbody>
{foreach from=$ships item=thisShip}{if ($thisShip.typ != "")}
{if ($thisShip.typ=='Moros')}{assign var="colorcode" value="#00b204"}
	{elseif ($thisShip.typ=='Naglfar')}{assign var="colorcode" value="#b20000"}
	{elseif ($thisShip.typ=='Phoenix')}{assign var="colorcode" value="#008eb2"}
	{elseif ($thisShip.typ=='Revelation')}{assign var="colorcode" value="#b2a800"}
{/if}
{assign var=s value=$thisShip.status}
  <tr bgcolor="{cycle values="#444444,#333333"}">
	<td align="center">{$thisShip.Id}</td>
	<td style="color:{$colorcode}">{$thisShip.typ}</td>
	<td>{$thisShip.name|escape:"htmlall"}</td>
	<td align="center"><font color="#{$color[$thisShip.status]}">{$language.$s}</font></td>
	<td align="center">{$thisShip.player|escape:"htmlall"}</td>
	<td align="center">{$thisShip.time|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center">{$thisShip.bemerkung|escape:"htmlall"}</td>
	<td align="center">{if ($thisShip.kblink != "")}<a href="{$thisShip.kblink}" target="_blank">link</a>{else}*{/if}</td>
	<td align="center"><a href="{$url_index}?module=Dread&action=deadedit&id={$thisShip.Id}"><img src="icons/wrench.png" alt="Edit" title="Edit"></a></td>
  </tr>
{/if}{/foreach}
{else}</thead>
  <tbody><tr bgcolor="#333333">
	<td colspan="9" style="text-align:center">{$language.no_dreadnought_available}</td>
  </tr>
{/if}  
  </tbody>
</table>
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}