{* Smarty *}{*debug*}
{************ AddHaulPage_MARATHON.tpl *************************************}

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
<img width="20" height="20" src="modules/MiningMax/images/ore/glitter.png">Add <input type="text" size="5" name="DarkGlitter" value="0"> Units of Dark glitter</td></tr><tr bgcolor="{cycle values="#444444,#333333"}"><td rowspan="" colspan="1"  width=""  >
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