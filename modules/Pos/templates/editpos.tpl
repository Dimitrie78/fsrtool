{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

<br/>

<link rel="stylesheet" type="text/css" href="modules/{$activeModule}/inc/settings.css">
<script type="text/javascript" src="modules/{$activeModule}/inc/anaus.js"></script>

<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}{/if}
<div>
<form action="{$index}" method="get">
 <input name="module" type="hidden" value="{$activeModule}">
 <input name="action" type="hidden" value="editPos">
 <select name="id" onchange="submit()">
{html_options options=$towers selected=$sel_tower}
</select>
</form>
</div>
<br>
<div>
<form action="{$url_dowork}" method="post">
<input type="hidden" name="module" value="{$activeModule}" />
<input type="hidden" name="action" value="edit" />
<input name="pos[id]" type="hidden" value="{$editpos->posID}" />

<table class="setti" cellpadding="2" cellspacing="0" border="0">
  <tr>
    <td colspan="2" align="center">{$editpos->icon64}</td>
  </tr>
  <tr>
    <td class="head" colspan="2" align="center">{$editpos->tower}</td>
  </tr>
  <tr>
    <td class="row">Status:</td>
	<td class="row2">{if $editpos->status == "4"}<span style="color:#090;font-weight:bold;">{$editpos->state}</span>{else}<span style="color:#F00;font-weight:bold;">{$editpos->state}</span>{/if}</td>
  </tr>
  <tr>
    <td class="row">Name:</td>
	<td class="row2">{$editpos->itemName}</td>
  </tr>
  <tr>
    <td class="row">Location:</td>
	<td class="row2">{$editpos->moon}</td>
  </tr>
  <tr>
    <td class="row">stateTimestamp:</td>
	<td class="row2">{$editpos->stateTimestamp}</td>
  </tr>
{if $canEdit}
  <tr>
    <td class="row">Manager:</td>
	<td class="row2"><input name="pos[manager]" type="text" value="{$editpos->manager}"></td>
  </tr>
{else}
   <tr>
    <td class="row">Manager:</td>
	<td class="row2">{$editpos->manager}</td>
  </tr>
{/if}
  <tr>
    <td class="row">Sovereignty:</td>
	<td class="row2">{if ($editpos->sov)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
{if $canEdit}
    <td class="mods" colspan="2">
    	SMA:<input name="pos[sma]" type="checkbox" {if $editpos->sma}checked="checked"{/if} />  
    	CHA:<input name="pos[cha]" type="checkbox" {if $editpos->cha}checked="checked"{/if} /> 
    	JB:<input name="pos[jb]" type="checkbox" {if $editpos->jb}checked="checked"{/if} />
        CJ:<input name="pos[cj]" type="checkbox" {if $editpos->cj}checked="checked"{/if} />
    </td>
  </tr>
  <tr>
    <td class="mods" colspan="2">
    	See Global:<input name="pos[global]" type="checkbox" {if $editpos->SeeGlobal}checked="checked"{/if} /> 
    </td>
  </tr>
{/if}
  <tr>
    <td class="head" colspan="2">General Configuration</td>
  </tr>
  <tr>
    <td class="row">Allow corporation members:</td>
	<td class="row2">{if ($editpos->allowCorporationMembers)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td class="row">Allow alliance members:</td>
	<td class="row2">{if ($editpos->allowAllianceMembers)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td class="head" colspan="2">Combat Configuration</td>
  </tr>
  <tr>
    <td class="row">use Ally Standings:</td>
	<td class="row2">{if ($editpos->useStandingsFrom)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td class="row">Attack if Standing lower than:</td>
	<td class="row2">{if ($editpos->onStandingDrop)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}&nbsp;{$editpos->onStandingDrop_standing}</td>
  </tr>
  <tr>
    <td class="row">Attack on status drop:</td>
	<td class="row2">{if ($editpos->onStatusDrop_enabled)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td class="row">Attack on aggression:</td>
	<td class="row2">{if ($editpos->onAggression)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td class="row">Attack when at war:</td>
	<td class="row2">{if ($editpos->onCorporationWar)}<span style="color:#090;font-weight:bold;">Yes</span>{else}<span style="color:#F00;font-weight:bold;">No</span>{/if}</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
{if $canEdit}
  <tr>
    <td colspan="2" align="center"><input type="submit" value="Update"></td>
  </tr>
{/if}
</table>
</form>
</div>
<br />
<div>
<a href="Javascript:aufzu('opti')">Optimal ({$editpos->fuel_opti.optimum_cycles_h})</a> | 
<a href="Javascript:aufzu('7')">7 Days</a> | 
<a href="Javascript:aufzu('14')">14 Days</a> | 
<a href="Javascript:aufzu('21')">21 Days</a> 
<table class="fuel" border="0" cellpadding="1" cellspacing="0">
  <tr>
    <td class="head">Fuel</td>
	<td class="head" id="opti" style="display:table-cell">Optimal</td>
	<td class="head" id="7" style="display:none">7 Days</td>
	<td class="head" id="14" style="display:none">14 Days</td>
	<td class="head" id="21" style="display:none">21 Days</td>
	<td class="head">Required</td>
  </tr>
  <tr>
    <td class="left">{$editpos->raseBlocks}</td>
	<td align="right" id="opti" style="display:table-cell">{$editpos->fuel_opti.optimum_Blocks|number_format}</td>
	<td align="right" id="7" style="display:none">{$editpos->fuel_opti.7day_Blocks|number_format}</td>
	<td align="right" id="14" style="display:none">{$editpos->fuel_opti.14day_Blocks|number_format}</td>
	<td align="right" id="21" style="display:none">{$editpos->fuel_opti.21day_Blocks|number_format}</td>
	<td align="right" id="opti" style="display:table-cell">{$editpos->fuel_diff.Blocks|number_format}</td>
	<td align="right" id="7" style="display:none">{$editpos->fuel_diff.7day_Blocks|number_format}</td>
	<td align="right" id="14" style="display:none">{$editpos->fuel_diff.14day_Blocks|number_format}</td>
	<td align="right" id="21" style="display:none">{$editpos->fuel_diff.21day_Blocks|number_format}</td>
  </tr>
{if ($editpos->fuel_opti.optimum_charters != "0")}
  <tr>
    <td class="left">Charters</td>
	<td align="right" id="opti" style="display:table-cell">{$editpos->fuel_opti.optimum_charters|number_format}</td>
	<td align="right" id="7" style="display:none">{$editpos->fuel_opti.7day_charters|number_format}</td>
	<td align="right" id="14" style="display:none">{$editpos->fuel_opti.14day_charters|number_format}</td>
	<td align="right" id="21" style="display:none">{$editpos->fuel_opti.21day_charters|number_format}</td>
	<td align="right" id="opti" style="display:table-cell">{$editpos->fuel_diff.charters|number_format}</td>
	<td align="right" id="7" style="display:none">{$editpos->fuel_diff.7day_charters|number_format}</td>
	<td align="right" id="14" style="display:none">{$editpos->fuel_diff.14day_charters|number_format}</td>
	<td align="right" id="21" style="display:none">{$editpos->fuel_diff.21day_charters|number_format}</td>
  </tr>
{/if}
  <tr>
    <td class="head">Capacity</td>
	<td class="head" align="right" id="opti" style="display:table-cell">{$editpos->fuel_diff.opti_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="7"    style="display:none">{$editpos->fuel_diff.7day_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="14"   style="display:none">{$editpos->fuel_diff.14day_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="21"   style="display:none">{$editpos->fuel_diff.21day_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="opti" style="display:table-cell">{$editpos->fuel_diff.diff_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="7"    style="display:none">{$editpos->fuel_diff.7day_diff_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="14"   style="display:none">{$editpos->fuel_diff.14day_diff_m3|number_format} m&sup3;</td>
	<td class="head" align="right" id="21"   style="display:none">{$editpos->fuel_diff.21day_diff_m3|number_format} m&sup3;</td>
  </tr>
</table>
</div>
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}