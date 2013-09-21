{* Smarty *}{*debug*}
{************ AddHaulPage.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; MiningMax - Haulern #{$runid} - {$location} -> {$selectedHaulLocation} </div>
<div id="menu">
<ul class="items">
	<li id="selected"><a href="{$index}">Aktuelle Mining Operationen</a></li>
	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

{* ------------------------------- Erzauswahl Start ------------------------ *}
 <thead>
  <tr>
   <th colspan="2">&raquo; Bitte Mengen eintragen</th>
 </thead>
   <tbody>
   	<tr><td>
   	<table>
     <tr><td>

<form action="{$index}&action=addhaul" method="post"><table width="100%" cellpadding="2" cellspacing="0">
<tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="1" colspan="1"  width="" align="left" >Hauling for Op: #<a href="index.php?action=show&id={$runid}">{$runid}</a></td>
<td rowspan="1" colspan="1"  width="" align="right" >System hauling to: 
{html_options name="location" values=$Systems output=$Systems selected=$selectedHaulLocation}

-or- <input type="text" name="location2" value=""></td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="1" colspan="2"  width=""  ><hr></td></tr>
<tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/arkonor.png">Add <input type="text" size="5" name="Arkonor" value="0"> Units of Arkonor</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/kernite.png">Add <input type="text" size="5" name="FieryKernite" value="0"> Units of Fiery kernite</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/arkonor.png">Add <input type="text" size="5" name="CrimsonArkonor" value="0"> Units of Crimson arkonor</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/mercoxit.png">Add <input type="text" size="5" name="Mercoxit" value="0"> Units of Mercoxit</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/arkonor.png">Add <input type="text" size="5" name="PrimeArkonor" value="0"> Units of Prime arkonor</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/mercoxit.png">Add <input type="text" size="5" name="MagmaMercoxit" value="0"> Units of Magma mercoxit</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/bistot.png">Add <input type="text" size="5" name="Bistot" value="0"> Units of Bistot</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/mercoxit.png">Add <input type="text" size="5" name="VitreousMercoxit" value="0"> Units of Vitreous mercoxit</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/bistot.png">Add <input type="text" size="5" name="TriclinicBistot" value="0"> Units of Triclinic bistot</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/omber.png">Add <input type="text" size="5" name="Omber" value="0"> Units of Omber</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/bistot.png">Add <input type="text" size="5" name="MonoclinicBistot" value="0"> Units of Monoclinic bistot</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/omber.png">Add <input type="text" size="5" name="SilveryOmber" value="0"> Units of Silvery omber</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/crokite.png">Add <input type="text" size="5" name="Crokite" value="0"> Units of Crokite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/omber.png">Add <input type="text" size="5" name="GoldenOmber" value="0"> Units of Golden omber</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/crokite.png">Add <input type="text" size="5" name="SharpCrokite" value="0"> Units of Sharp crokite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/spodumain.png">Add <input type="text" size="5" name="BrightSpodumain" value="0"> Units of Bright Spodumain</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/crokite.png">Add <input type="text" size="5" name="CrystallineCrokite" value="0"> Units of Crystalline crokite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/spodumain.png">Add <input type="text" size="5" name="Spodumain" value="0"> Units of Spodumain</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/ochre.png">Add <input type="text" size="5" name="DarkOchre" value="0"> Units of Dark Ochre</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/spodumain.png">Add <input type="text" size="5" name="GleamingSpodumain" value="0"> Units of Gleaming spodumain</td></tr><tr bgcolor="{cycle values="#444444,#333333"}">
<td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/ochre.png">Add <input type="text" size="5" name="OnyxOchre" value="0"> Units of Onyx ochre</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/plagioclase.png">Add <input type="text" size="5" name="Plagioclase" value="0"> Units of Plagioclase</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/ochre.png">Add <input type="text" size="5" name="ObsidianOchre" value="0"> Units of Obsidian ochre</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/plagioclase.png">Add <input type="text" size="5" name="AzurePlagioclase" value="0"> Units of Azure plagioclase</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/gneiss.png">Add <input type="text" size="5" name="Gneiss" value="0"> Units of Gneiss</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/plagioclase.png">Add <input type="text" size="5" name="RichPlagioclase" value="0"> Units of Rich plagioclase</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/gneiss.png">Add <input type="text" size="5" name="IridescentGneiss" value="0"> Units of Iridescent gneiss</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/pyroxeres.png">Add <input type="text" size="5" name="Pyroxeres" value="0"> Units of Pyroxeres</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/gneiss.png">Add <input type="text" size="5" name="PrismaticGneiss" value="0"> Units of Prismatic gneiss</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/pyroxeres.png">Add <input type="text" size="5" name="SolidPyroxeres" value="0"> Units of Solid pyroxeres</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hedbergite.png">Add <input type="text" size="5" name="Hedbergite" value="0"> Units of Hedbergite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/pyroxeres.png">Add <input type="text" size="5" name="ViscousPyroxeres" value="0"> Units of Viscous pyroxeres</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hedbergite.png">Add <input type="text" size="5" name="GlazedHedbergite" value="0"> Units of Glazed hedbergite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/scordite.png">Add <input type="text" size="5" name="Scordite" value="0"> Units of Scordite</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hemorphite.png">Add <input type="text" size="5" name="Hemorphite" value="0"> Units of Hemorphite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/scordite.png">Add <input type="text" size="5" name="CondensedScordite" value="0"> Units of Condensed scordite</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hedbergite.png">Add <input type="text" size="5" name="VitricHedbergite" value="0"> Units of Vitric hedbergite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/scordite.png">Add <input type="text" size="5" name="MassiveScordite" value="0"> Units of Massive scordite</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hemorphite.png">Add <input type="text" size="5" name="VividHemorphite" value="0"> Units of Vivid hemorphite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/veldspar.png">Add <input type="text" size="5" name="Veldspar" value="0"> Units of Veldspar</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/hemorphite.png">Add <input type="text" size="5" name="RadiantHemorphite" value="0"> Units of Radiant hemorphite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/veldspar.png">Add <input type="text" size="5" name="ConcentratedVeldspar" value="0"> Units of Concentrated veldspar</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/jaspet.png">Add <input type="text" size="5" name="Jaspet" value="0"> Units of Jaspet</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/veldspar.png">Add <input type="text" size="5" name="DenseVeldspar" value="0"> Units of Dense veldspar</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/jaspet.png">Add <input type="text" size="5" name="PureJaspet" value="0"> Units of Pure jaspet</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/glitter.png">Add <input type="text" size="5" name="DarkGlitter" value="0"> Units of Dark glitter</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/jaspet.png">Add <input type="text" size="5" name="PristineJaspet" value="0"> Units of Pristine jaspet</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/gelidus.png">Add <input type="text" size="5" name="Gelidus" value="0"> Units of Gelidus</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/kernite.png">Add <input type="text" size="5" name="Kernite" value="0"> Units of Kernite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/krystallos.png">Add <input type="text" size="5" name="Krystallos" value="0"> Units of Krystallos</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/kernite.png">Add <input type="text" size="5" name="LuminousKernite" value="0"> Units of Luminous kernite</td><td rowspan="" colspan="1"  width=""  >
<img width="20" height="20" src="modules/MiningMax/images/ore/glaze.png">Add <input type="text" size="5" name="PristineWhiteGlaze" value="0"> Units of Pristine white glaze</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="1" colspan="2"  width=""  ><hr></td></tr>

<tr bgcolor="{cycle values="#444444,#333333"}">
<td rowspan="1" colspan="2"  width="" align="center" ><b><input type="submit" name="haul" value="Commit haul to database"></b></td></tr>
</table>

<input type="hidden" value="check" name="check">
<input type="hidden" value="addhaul" name="action">
<input type="hidden" value="{$runid}" name="runid">
</form>

</table>

     
     
 	</table>
  </td></tr>
  
  </tbody>
{* ------------------------------- Auszahlungsinfos Ende ------------------------ *}


{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}