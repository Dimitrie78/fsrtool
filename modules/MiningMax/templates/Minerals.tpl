{* Smarty *}
{************ Minerals.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
{assign var=title value="Mineralienpreise"}
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/menu.tpl"}
{* ------------------------------- *}

<table class="data" cellspacing="0" cellpadding="3">
 <thead> 
  <tr>
    <td colspan="{$mineralprices|@count}"><span class="head">Aktuelle Mineralien Preise in ISK</span></td>
  </tr>
  <tr>
    <td colspan="{$mineralprices|@count}">Preise vom {$date|date_format:"%d.%m.%Y %H:%M"}</td>
  </tr>
  <tr class="headcol">
   {foreach from=$mineralprices item=mineral}
   <td align="center">{$mineral.Name}</td>
   {/foreach}
  </tr>
 </thead>
 <tbody>
  <tr>
   {foreach from=$mineralprices item=mineral}
    <td align="center">{$mineral.Price|commify:2:',':'.'}</td>
	 {/foreach}
  </tr>
 </tbody>
 {if ($MySelf->canChangeOre() == "1") OR $curUser->admin == 1}
 <tfoot>
  <form action="{$url_dowork_mins}" method="post">
  <input type="hidden" name="mb_id" value="{$MySelf->getID()}" />
  <tr>
	{foreach from=$mineralprices item=mineral}
		<td align="center"><input type="text" name="Minerals[{$mineral.Name}]" size="8" value="{$mineral.Price|commify:2:',':'.'}" style="text-align:center" /></td>
	{/foreach}
    </tr><tr>
	    <td colspan={$mineralprices|@count}" align="center"><input type="submit" name="update" value="Preise aktualisieren" /></td>
	  </tr></form>
 </tfoot>
 {/if}
</table>

<br/>
{*<pre>
ID: {$ov.id}-{$ov.modifier}
Preise vom: {$ov.time|date_format:"%d.%m.%Y %H:%M"}
</pre>*}

<table class="data" cellspacing="0" cellpadding="3">
	<thead>
		<tr>
	    <td colspan="{$mineralprices|@count}"><span class="head">Aktuelle Erzpreise in ISK</span></td>
	  </tr>
		<tr class="headcol"><td>Eis</td><td align="right">Preis</td>  </tr> </thead>
<tbody>
 
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>DarkGlitter<td align="right">{$ov.DarkGlitterWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Gelidus<td align="right">{$ov.GelidusWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>GlareCrust<td align="right">{$ov.GlareCrustWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>PristineWhiteGlaze<td align="right">{$ov.PristineWhiteGlazeWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>WhiteGlaze<td align="right">{$ov.WhiteGlazeWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Krystallos<td align="right">{$ov.KrystallosWorth|commify:2:',':'.'}
 
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>BlueIce<td align="right">{$ov.BlueIceWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>ClearIcicle<td align="right">{$ov.ClearIcicleWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>EnrichedClearIcicle<td align="right">{$ov.EnrichedClearIcicleWorth|commify:2:',':'.'}
 
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>GlacialMass<td align="right">{$ov.GlacialMassWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>SmoothGlacialMass<td align="right">{$ov.SmoothGlacialMassWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>ThickBlueIce<td align="right">{$ov.ThickBlueIceWorth|commify:2:',':'.'}
 
 
 
 <thead>   <tr class="headcol"><td>Erz</td><td  align="right">Preis</td>  </tr> </thead>

 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Mercoxit<td align="right">{$ov.MercoxitWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>MagmaMercoxit<td align="right">{$ov.MagmaMercoxitWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>VitreousMercoxit<td align="right">{$ov.VitreousMercoxitWorth|commify:2:',':'.'}

 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Arkonor<td align="right">{$ov.ArkonorWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>CrimsonArkonor<td align="right">{$ov.CrimsonArkonorWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>PrimeArkonor<td align="right">{$ov.PrimeArkonorWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Bistot<td align="right">{$ov.BistotWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>MonoclinicBistot<td align="right">{$ov.MonoclinicBistotWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>TriclinicBistot<td align="right">{$ov.TriclinicBistotWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Crokite<td align="right">{$ov.CrokiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>CrystallineCrokite<td align="right">{$ov.CrystallineCrokiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>SharpCrokite<td align="right">{$ov.SharpCrokiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>BrightSpodumain<td align="right">{$ov.BrightSpodumainWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>GleamingSpodumain<td align="right">{$ov.GleamingSpodumainWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Spodumain<td align="right">{$ov.SpodumainWorth|commify:2:',':'.'}

 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Hedbergite<td align="right">{$ov.HedbergiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>GlazedHedbergite<td align="right">{$ov.GlazedHedbergiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>VitricHedbergite<td align="right">{$ov.VitricHedbergiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Hemorphite<td align="right">{$ov.HemorphiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>VividHemorphite<td align="right">{$ov.VividHemorphiteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>RadiantHemorphite<td align="right">{$ov.RadiantHemorphiteWorth|commify:2:',':'.'}

 <tr bgcolor="{cycle values="#444444,#333333"}"><td>DarkOchre<td align="right">{$ov.DarkOchreWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>ObsidianOchre<td align="right">{$ov.ObsidianOchreWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>OnyxOchre<td align="right">{$ov.OnyxOchreWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Gneiss<td align="right">{$ov.GneissWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>IridescentGneiss<td align="right">{$ov.IridescentGneissWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>PrismaticGneiss<td align="right">{$ov.PrismaticGneissWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Jaspet<td align="right">{$ov.JaspetWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>PureJaspet<td align="right">{$ov.PureJaspetWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>PristineJaspet<td align="right">{$ov.PristineJaspetWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Kernite<td align="right">{$ov.KerniteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>FieryKernite<td align="right">{$ov.FieryKerniteWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>LuminousKernite<td align="right">{$ov.LuminousKerniteWorth|commify:2:',':'.'}

 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Omber<td align="right">{$ov.OmberWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>SilveryOmber<td align="right">{$ov.SilveryOmberWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>GoldenOmber<td align="right">{$ov.GoldenOmberWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Plagioclase<td align="right">{$ov.PlagioclaseWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>AzurePlagioclase<td align="right">{$ov.AzurePlagioclaseWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>RichPlagioclase<td align="right">{$ov.RichPlagioclaseWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Pyroxeres<td align="right">{$ov.PyroxeresWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>SolidPyroxeres<td align="right">{$ov.SolidPyroxeresWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>ViscousPyroxeres<td align="right">{$ov.ViscousPyroxeresWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Scordite<td align="right">{$ov.ScorditeWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>CondensedScordite<td align="right">{$ov.CondensedScorditeWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>MassiveScordite<td align="right">{$ov.MassiveScorditeWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>Veldspar<td align="right">{$ov.VeldsparWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>ConcentratedVeldspar<td align="right">{$ov.ConcentratedVeldsparWorth|commify:2:',':'.'}
 <tr bgcolor="{cycle values="#444444,#333333"}"><td>DenseVeldspar<td align="right">{$ov.DenseVeldsparWorth|commify:2:',':'.'}
</td>
</tr>
</table>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}