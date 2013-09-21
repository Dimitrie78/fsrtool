{include file="header.tpl"}
{include file="file:[Silo]menue.tpl"}

<div style="width:1000px">

{foreach from=$minTime item=time key=sys}
<br />{$sys} - {$time.days} - {$time.time}
{/foreach}
<br />
<div style="text-align:left;border-bottom:solid 3px #000;padding:5px;"><b>{$language.silos_that_require_maintenance}</b></div><br/>
{if $Towers}
{include file="file:[Silo]showSilos.tpl"}
{else}{$language.silos_do_not_require_maintenace}
{/if}
<br/><br/><br/>

<div style="text-align:left;border-bottom:solid 3px #000;padding:5px;"><b>{$language.silos_that_are_not_yet_assigned}</b></div><br/>

{if isset($Silos)}
<p>
<form action="{$index}" method="post">
<input type="hidden" name="action" value="Silos">
<input type="submit" value="Auto assign" name="auto" id="auto"/>
</form>
</p>
<table cellpadding="3" cellspacing="0" class="data" style="width:500px; font-size:10px">
 <thead>
  <tr>
   <td>Location</td>
   <td>&nbsp;</td>
   <td>Type</td>
   <td>{$language.amount}</td>
   <td>Select Moon</td>
  </tr>
 </thead>
 <tbody>
<form action="{$index}" method="post">
<input type="hidden" name="action" value="Silos">
{foreach from=$Silos item=silo}
{cycle name=c values="#333333,#444444" assign=col}
  <tr bgcolor="{$col}">
   <td>{$silo.location}</td>
   <td>{if $silo.type != 'undefined'}{$silo.typePic}{else}&nbsp;{/if}</td>
   <td>{$silo.type}</td>
   <td align="right">{$silo.quantity}</td>
   <td align="center"><select name="moonID[]" style="width:175px">{html_options options=$silo.moonIDs}</select></td>
       <input type="hidden" name="itemID[]" value="{$silo.itemID}">
  </tr>
{/foreach}
 </tbody>
 <tfoot><tr>
  <td colspan="5" align="center"><input type="submit" value="{$language.assign}"  /></td>
 </tr></tfoot>
</form>

</table>
{else}{$language.silos_are_all_assigned}
{/if}
</div>

 
{include file="footer.tpl"}