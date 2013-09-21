{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

<br/>

<script type="text/javascript" src="modules/{$activeModule}/inc/pos.js"></script>

<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}{/if}
{if ($towers)}
<form action="{$index}&action=fuelBill" method="post">
<input type="hidden" name="module" value="{$activeModule}">
<input type="hidden" name="action" value="fuelBill">

<div id="saveFilterWin" style="display: none;"></div>
<p>
{html_options name='filter' options=$fuel_filter selected=$sel_fuel_filter}
<input type="submit" name="delFilter" value="Delete Filter" />
<input type="button" value="Save new Filter" onclick="showSaveFilterWin()" />
<br /><br />
Optimal?<input type="checkbox" name="optimal" {if $optimal_fuel}checked="checked"{/if}/> or 
Days <input type="text" name="days_to_refuel" size="5" value="{$days_to_refuel}" />
{html_options name='regionID' 			options=$optregions 		selected=$regionID}
{html_options name='consteID' 			options=$optconstellations  selected=$consteID}
{html_options name='systemID' 			options=$optsystems 		selected=$systemID}
{html_options name='use_current_level'  options=$optlevels 			selected=$use_current_level}
<input type="submit" name="submit" value="Filter" />
<br />Negative fuel values?<input type="checkbox" name="negative_fuel" {if $negative_fuel}checked="checked"{/if}/>
</p>

<table cellpadding="2" cellspacing="1" style="border:2px solid #000">
  <tr style="background-color:#4F0202">
    <td align="center">#</td>	
	<td align="center">Region</td>
	<td align="center">Moon</td>
    <td align="center">Amarr Fuel Block</td>
    <td align="center">Caldari Fuel Block</td>
    <td align="center">Gallente Fuel Block</td>
    <td align="center">Minmatar Fuel Block</td>
	<td align="center">Charters</td>
    <td align="center">&nbsp;</td>
  </tr>
{assign var='linecount' value=0}
{foreach from=$towers item=thisPos}
{if $linecount eq 10}
  <tr style="background-color:#4F0202">
    <td align="center">#</td>	
	<td align="center">Region</td>
	<td align="center">Moon</td>
    <td align="center">Amarr Fuel Block</td>
    <td align="center">Caldari Fuel Block</td>
    <td align="center">Gallente Fuel Block</td>
    <td align="center">Minmatar Fuel Block</td>
	<td align="center">Charters</td>
    <td align="center">&nbsp;</td>
  </tr>
{assign var='linecount' value=0}
{/if}
  <tr bgcolor="{cycle values="#222222,#333333"}">
    <td align="right">{counter}</td>
    <td align="center">{$thisPos.region}</td>
    <td align="center">{$thisPos.moon}</td>
    <td align="right">{$thisPos.required_Amarr|number_format}</td>
    <td align="right">{$thisPos.required_Caldari|number_format}</td>
    <td align="right">{$thisPos.required_Gallente|number_format}</td>
    <td align="right">{$thisPos.required_Minmatar|number_format}</td>
	<td align="right">{$thisPos.required_charters|number_format}</td>
    <td align="center"><input type="checkbox" name="pos_ids[{$thisPos.posID}]" {if isset($optposids[$thisPos.posID])}checked="checked"{/if} /></td>
  </tr>
{math equation="x+y" x=$linecount y=1 assign='linecount'}
{/foreach}
 <tr style="background-color:#4F0202">
    <td align="left" colspan="3">Total m&sup3;</td>	
    <td align="right">{$fuel_Amarr_size|number_format}</td>
    <td align="right">{$fuel_Caldari_size|number_format}</td>
    <td align="right">{$fuel_Gallente_size|number_format}</td>
    <td align="right">{$fuel_Minmatar_size|number_format}</td>
	<td align="right">{$fuel_charters_size|number_format}</td>
    <td align="center">&nbsp;</td>
  </tr>
  <tr style="background-color:#4F0202">
    <td align="left" colspan="3">Totals</td>	
    <td align="right">{$fuel_Amarr|number_format}</td>
    <td align="right">{$fuel_Caldari|number_format}</td>
    <td align="right">{$fuel_Gallente|number_format}</td>
    <td align="right">{$fuel_Minmatar|number_format}</td>
	<td align="right">{$fuel_charters|number_format}</td>
    <td align="center">&nbsp;</td>
  </tr>
  <tr style="background-color:#000">
    <td align="left" colspan="3">Total Price</td>	
    <td align="right">{$price_Amarr|number_format}</td>
    <td align="right">{$price_Caldari|number_format}</td>
    <td align="right">{$price_Gallente|number_format}</td>
    <td align="right">{$price_Minmatar|number_format}</td>
	<td align="right">{$price_charters|number_format}</td>
    <td align="center">&nbsp;</td>
  </tr>
  <tr style="background-color:#000">
    <td align="center" colspan="15">{$total_size|number_format}&nbsp;(m&sup3;)&nbsp;&nbsp;{$price_total|number_format}&nbsp;ISK</td>
  </tr>
</table>
<p><input type="submit" name="submit" value="Filter" /></p>


{include file="file:[Pos]hanger.tpl"}
</form>
<p><textarea name="fuel Bill" cols="50" rows="10">
Amarr Fuel Block: {$fuel_Amarr|number_format} ({$fuel_Amarr_size|number_format}m&sup3;)
Caldari Fuel Block: {$fuel_Caldari|number_format} ({$fuel_Caldari_size|number_format}m&sup3;)
Gallente Fuel Block: {$fuel_Gallente|number_format} ({$fuel_Gallente_size|number_format}m&sup3;)
Minmatar Fuel Blocks: {$fuel_Minmatar|number_format} ({$fuel_Minmatar_size|number_format}m&sup3;)

Charters: {$fuel_charters|number_format} ({$fuel_charters_size|number_format}m&sup3;)

Total in m&sup3;: {$total_size|number_format}
</textarea></p>

{else}
There are no {$Status} Towers
{/if}

</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}