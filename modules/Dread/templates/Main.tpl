{* Smarty *}
{************ Dread.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('form#dreadsel a').click(function(){
		var dread = $(this).attr('href').replace(/#/g,'');
		$('form#dreadsel input').attr('value',dread);
		$('form#dreadsel').submit();
	}).attr('title','Details/Ausgabe');
});
</script>{/literal}

<div id="title">&raquo; Dreadtool</div>
<div id="menu">
<ul class="items">
	<li id="selected"><a href="{$url_index}?module=Dread&action=main">{$language.overview}</a></li>
	<li><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

<div align="center">
<table class="data" cellpadding="3" cellspacing="0">
 <thead>
 <tr>
	<td colspan="7"><span class="head">Dreadnoughts {$language.of}&nbsp;{$curUser->corpName}</span> {if $ships}({$ships|@count} {$language.pieces}){/if}</td>
	<td colspan="5" style="text-align:right;">{if $ships}
		<form id="dreadsel" action="{$url_index}?module=Dread&action=ausgabe" method="post"><input type="hidden" name="rasse" value="0" />
			{$vorhanden.Moros+$vorhanden.Naglfar+$vorhanden.Revelation+$vorhanden.Phoenix} {$language.ready_for_action}: 
			<a href="#Moros">{$vorhanden.Moros} Moros</a>,
			<a href="#Naglfar">{$vorhanden.Naglfar} Naglfar</a>,
			<a href="#Revelation">{$vorhanden.Revelation} Revelation</a>,
			<a href="#Phoenix">{$vorhanden.Phoenix} Phoenix</a>
		</form>{/if}
	</td>
 </tr>
 {if $ships}
 <tr class="headcol">
    <td align="center"><a href="{$url_index}?module=Dread&sort=id">#</a></td>
	<td><a href="{$url_index}?module=Dread&sort=typ">Typ</a></td>
	<td><a href="{$url_index}?module=Dread&sort=name">Name</a></td>
	<td align="center"><a href="{$url_index}?module=Dread&sort=standort">{$language.location}</a></td>
	<td align="center"><a href="{$url_index}?module=Dread&sort=status">{$language.status}</a></td>
	<td align="center"><a href="{$url_index}?module=Dread&sort=versichert">{$language.insured}</a></td>
	<td align="center" width="125"><a href="{$url_index}?module=Dread&sort=bemerkung">{$language.comment}</a></td>
	<td align="center" width="125"><a href="{$url_index}?module=Dread&sort=player">{$language.issued_to}</a></td>
	<td align="center" width="110"><a href="{$url_index}?module=Dread&sort=time">{$language.issued_on}</a></td>
	<td align="center" width="110"><a href="{$url_index}?module=Dread&sort=timeback">{$language.back_on}</a></td>
	<td align="center">*</td>
  </tr></thead><tbody>
{foreach from=$ships item=thisShip}{if ($thisShip.typ != "")}
{if ($thisShip.typ=='Moros')}{assign var="colorcode" value="#00b204"}
	{elseif ($thisShip.typ=='Naglfar')}{assign var="colorcode" value="#b20000"}
	{elseif ($thisShip.typ=='Phoenix')}{assign var="colorcode" value="#008eb2"}
	{elseif ($thisShip.typ=='Revelation')}{assign var="colorcode" value="#b2a800"}
{/if}
{assign var=s value=$thisShip.status}
{assign var=v value=$thisShip.versichert}
  <tr bgcolor="{cycle values="#444444,#333333"}">
	<td align="center">{$thisShip.Id}</td>
	<td style="color:{$colorcode}">{$thisShip.typ}</td>
	<td>{$thisShip.name|escape:"htmlall"}</td>
	<td align="center">{$thisShip.standort|escape:"htmlall"}</td>
	<td align="center"><font color="#{$color[$thisShip.status]}">{$language.$s}</font></td>
	<td align="center"><font color="#{$color[$thisShip.versichert]}">{$language.$v}</font></td>
	<td align="center">{$thisShip.bemerkung|escape:"htmlall"}</td>
	<td align="center">{$thisShip.player|escape:"htmlall"}</td>
	<td align="center">{$thisShip.time|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center">{$thisShip.timeback|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center"><a href="{$url_index}?module=Dread&action=edit&id={$thisShip.Id}"><img src="icons/wrench.png" alt="Edit" title="Edit"></a></td>
  </tr>
{/if}{/foreach}
</tbody>
{else}
  </thead><tbody><tr bgcolor="#333333">
	<td colspan="12" style="text-align:center">{$language.no_dreadnought_available}</td>
  </tr></tbody>
{/if}
</table>
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}