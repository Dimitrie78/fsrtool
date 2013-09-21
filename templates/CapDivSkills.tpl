{* Smarty *}
{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

<div id="title">&raquo; CapDiv Skills</div>
{include file="menu.tpl"}
<br />

<script type="text/javascript" src="inc/CapDivSkills.js"></script>

{foreach from=$skills item=thisChar}
<p align="center">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="130" align="center"><img src="{getportrait charID=$thisChar.charID size=64}"><br>{$thisChar.charName}</td>
	<td width="300">
		<table border="0">
		  <tr>
		    <td width="20">{if $thisChar.fly.Revelation == 1}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>
		  	<td>Amarr Dreadnought ready</td>
		  </tr>
		  <tr>
		    <td width="20">{if $thisChar.fly.Phoenix == 1}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>	
		  	<td>Caldari Dreadnought ready</td>
		  </tr>
		  <tr>
		    <td width="20">{if $thisChar.fly.Moros == 1}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>		
		  	<td>Gallente Dreadnought ready</td>
		  </tr>
		  <tr>
		  	<td width="20">{if $thisChar.fly.Naglfar  == 1}<img src="icons/tick.png">{else}<img src="icons/cross.png">{/if}</td>		
		  	<td>Minmatar Dreadnought ready</td>
		  </tr>
		</table>
	</td>
  </tr>
</table>
</p>
{*
{/foreach}

<table cellspacing="0" cellpadding="3">
 <tr valign="top">
{foreach from=$skills item=thisChar}
  <td align="center" width="100"><img onclick="toggleUser('{$thisChar.charID}')" style="border: 1px solid gray; cursor: pointer" alt="{$thisChar.charName}" src="{getportrait charID=$thisChar.charID size=64}" /><p>{$thisChar.charName}</p></td>
{/foreach}
 </tr>
</table>

{foreach from=$skills item=thisChar name=Char}

{if $smarty.foreach.Char.first}
<div class="user {$thisChar.charID}" style="display:block ">
{else}
<div class="user {$thisChar.charID}" style="display:none ">
{/if}
*}
<table cellspacing="0" cellpadding="2"  style="width:800px; border: solid 3px #000; ">
<thead  style="background: black"> 
 <tr>
  <th colspan="6" style="background: #333333">{$thisChar.charName}</th>
 </tr>
 <tr>
  <th colspan="2">Capital Ships</th>
  <th colspan="2">Carrier</th>
  <th colspan="2">Dread</th>
 </tr>
</thead>
<tbody style="background: #333333">
 <tr valign="top">
  <td colspan="2" align="center" style="border: solid 1px #000 ">
   <table class="miningDiv" cellspacing="0" cellpadding="2" summary="Capital Ships">
    {foreach from=$thisChar.Amarr  item=Amarr name=Ama}
	{assign var='color' value=$bgcolor[$Amarr.level]}
	<tr>
	 {if $smarty.foreach.Ama.first}
	 <td width="100">Amarr</td>
	 {else}
	 <td width="100">&nbsp;</td>
	 {/if}
	 <td style="color:{$color}" width="130">{$Amarr.name}</td>
	 <td style="color:{$color}">{$Amarr.level}</td>
	</tr>
	{/foreach}
	<tr><td colspan="3">&nbsp;</td></tr>
	{foreach from=$thisChar.Caldari item=Caldari name=Cali}
	{assign var='color' value=$bgcolor[$Amarr.level]}
	<tr>
	 {if $smarty.foreach.Cali.first}
	 <td width="100">Caldari</td>
	 {else}
	 <td width="100">&nbsp;</td>
	 {/if}
	 <td style="color:{$color}" width="130">{$Caldari.name}</td>
	 <td style="color:{$color}">{$Caldari.level}</td>
	</tr>
	{/foreach}
	<tr><td colspan="3">&nbsp;</td></tr>
	{foreach from=$thisChar.Gallente item=Gallente name=Gal}
	{assign var='color' value=$bgcolor[$Gallente.level]}
	<tr>
	 {if $smarty.foreach.Gal.first}
	 <td width="100">Gallente</td>
	 {else}
	 <td width="100">&nbsp;</td>
	 {/if}
	 <td style="color:{$color}" width="130">{$Gallente.name}</td>
	 <td style="color:{$color}">{$Gallente.level}</td>
	</tr>
	{/foreach}
	<tr><td colspan="3">&nbsp;</td></tr>
	{foreach from=$thisChar.Minmatar  item=Minmatar name=Mini}
	{assign var='color' value=$bgcolor[$Minmatar.level]}
	<tr>
	 {if $smarty.foreach.Mini.first}
	 <td width="100">Minmatar</td>
	 {else}
	 <td width="100">&nbsp;</td>
	 {/if}
	 <td style="color:{$color}" width="130">{$Minmatar.name}</td>
	 <td style="color:{$color}">{$Minmatar.level}</td>
	</tr>
	{/foreach}
   </table>
  </td>

  <td colspan="2" align="center" style="border: solid 1px #000 ">
   <table class="miningDiv" cellspacing="0" cellpadding="2" summary="Carrier">
    
	{foreach from=$thisChar.Carrier item=Carrier}
	{assign var='color' value=$bgcolor[$Carrier.level]}
	<tr>
	 <td style="color:{$color}" colspan="2" width="230">{$Carrier.name}</td>
	 <td style="color:{$color}">{$Carrier.level}</td>
	</tr>
	{/foreach}
   </table>
  </td>

  <td colspan="2" align="center" style="border: solid 1px #000 ">
   <table class="miningDiv" cellspacing="0" cellpadding="2" summary="Dread">
    {foreach from=$thisChar.Dread item=Dread}
	{assign var='color' value=$bgcolor[$Dread.level]}
	<tr>
	 <td style="color:{$color}" width="230">{$Dread.name}</td>
	 <td style="color:{$color}">{$Dread.level}</td>
	</tr>
	{/foreach}
   </table>
  </td>
 </tr>

</tbody>
</table>

{/foreach}

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}