{* Smarty *}
{************ Dread.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; Dreadtool</div>
<div id="menu">
<ul class="items">
	<li><a href="{$url_index}?module=Dread&action=main">{$language.overview}</a></li>
	<li id="selected"><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

{if ($mySelect=='Moros')}{assign var="colorcode" value="#00b204"}
	{elseif ($mySelect=='Naglfar')}{assign var="colorcode" value="#b20000"}
	{elseif ($mySelect=='Phoenix')}{assign var="colorcode" value="#008eb2"}
	{elseif ($mySelect=='Revelation')}{assign var="colorcode" value="#b2a800"}
{/if}

<div align="center">
{if ($mySelect)}
<table class="data" cellpadding="0" cellspacing="0" style="border: solid 3px {$colorcode};">
 <thead style="background-color:{$colorcode};">
 <tr>
	<td colspan="6"">
		<span class="head">{$mySelect} {$language.of}&nbsp;{$curUser->corpName}</span> ({$shipTyp|@count} {$language.pieces})
	</td>
	<td colspan="3" style="text-align:right;">
		<form action="{$url_index}?module=Dread&action=ausgabe" method="post">
			{$language.choose_another_dread}: <select name="rasse" onChange="submit()">
				{html_options options=$ships selected=$mySelect}
			</select>
		</form>
	</td>
 </tr><tr class="headcol">
    <td align="center">#</td>
	<td>Name</td>
	<td align="center">{$language.location}</td>
	<td align="center">{$language.status}</td>
	<td align="center">{$language.insured}</td>
	<td>{$language.comment}</td>
	<td align="center" width="125">{$language.issued_to}</td>
	<td align="center" width="125">{$language.issued_on}</td>
	<td align="center" width="125">{$language.back_on}</td>
  </tr></thead>
  <tbody>
{foreach from=$shipTyp item=thisShip}{if ($thisShip.typ != "")}
{assign var=s value=$thisShip.status}
{assign var=v value=$thisShip.versichert}
{if ($thisShip.status == "not_ready")}
<form method="post" action="{$url_index}?module=Dread&action=ausgabe">
  <tr bgcolor="{cycle values="#444444,#333333"}">
	<td align="center">{$thisShip.Id}</td>
	<td>{$thisShip.name|escape:"htmlall"}</td>
	<td align="center">{$thisShip.standort|escape:"htmlall"}</td>
	<td align="center"><font color="#{$color[$thisShip.status]}">{$language.$s}</font></td>
	<td align="center"><font color="#{$color[$thisShip.versichert]}">{$language.$v}</font></td>
	<td>{$thisShip.bemerkung|escape:"htmlall"}</td>
	<td align="center">{$thisShip.player|escape:"htmlall"}</td>
	<td align="center">{$thisShip.time|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center">{$thisShip.timeback|date_format:"%d.%m.%Y %H:%M"}</td>
  </tr>
<input type="hidden" name="id" value="{$thisShip.Id}" />
<input type="hidden" name="rasse" value="{$thisShip.typ}" />
</form>
{elseif ($thisShip.status == "verliehen")}
<form method="post" action="{$url_dowork}">
  <tr bgcolor="{cycle values="#444444,#333333"}">
	<td align="center">{$thisShip.Id}</td>
	<td>{$thisShip.name|escape:"htmlall"}</td>
	<td align="center">{$thisShip.standort|escape:"htmlall"}</td>
	<td align="center"><font color="#{$color[$thisShip.status]}">{$language.$s}</font></td>
	<td align="center"><font color="#{$color[$thisShip.versichert]}">{$language.$v}</font></td>
	<td>{$thisShip.bemerkung|escape:"htmlall"}</td>
	<td align="center">{$thisShip.player|escape:"htmlall"}</td>
	<td align="center">{$thisShip.time|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center"><input type="submit" name="back" value="&laquo; {$language.return}" /></td>
  </tr>
<input type="hidden" name="id" value="{$thisShip.Id}" />
<input type="hidden" name="rasse" value="{$thisShip.typ}" />
<input type="hidden" name="module" value="Dread" />
<input type="hidden" name="action" value="back" />
</form>
{else}
<form method="post" action="{$url_dowork}">
  <tr bgcolor="{cycle values="#444444,#333333"}">
	<td align="center">{$thisShip.Id}</td>
	<td>{$thisShip.name|escape:"htmlall"}</td>
	<td align="center">{$thisShip.standort|escape:"htmlall"}</td>
	<td align="center"><font color="#{$color[$thisShip.status]}">{$language.$s}</font></td>
	<td align="center"><font color="#{$color[$thisShip.versichert]}">{$language.$v}</font></td>
	<td>{$thisShip.bemerkung|escape:"htmlall"}</td>
	<td align="center"><select name="player" onChange="submit()"><option value="0">{$language.unspent}</option><optgroup label="{$language.authorized_pilot}:">{html_options options=$canFly}</optgroup></select></td>
	<td align="center">{$thisShip.time|date_format:"%d.%m.%Y %H:%M"}</td>
	<td align="center">{$thisShip.timeback|date_format:"%d.%m.%Y %H:%M"}</td>
  </tr>
<input type="hidden" name="id" value="{$thisShip.Id}" />
<input type="hidden" name="rasse" value="{$thisShip.typ}" />
<input type="hidden" name="module" value="Dread" />
<input type="hidden" name="action" value="ausgabe" />
</form>
{/if}
{/if}{/foreach}
</tbody></table>
{else}
<form action="{$url_index}?module=Dread&action=ausgabe" method="post">
  {$language.select_dread}: <select name="rasse" onChange="submit()">
    <option value="0">-----</option>{html_options options=$ships selected=$mySelect}
  </select>
</form>
{/if}
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}