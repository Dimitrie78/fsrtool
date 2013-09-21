{include file="header.tpl"}    
{include file="file:[Silo]menue.tpl"}

<div align="center">

<h2>{$language.silos_that_are_not_yet_assigned}</h2>

<table cellpadding="3" cellspacing="0" class="data" style="width:500px; font-size:10px">
 <thead>
  <tr>
   <td>Location</td>
   <td>Type</td>
   <td>{$language.amount}</td>
   <td>Select Moon</td>
  </tr>
 </thead>
 <tbody>
<form action="{$index}" method="post">
<input type="hidden" name="action" value="Silos">
{foreach from=$Silos item=silo}
  <tr>
   <td>{$silo.location}</td>
   <td>{$silo.type}</td>
   <td>{$silo.quantity}</td>
   <td><select name="moonID[]">{html_options options=$silo.moonIDs}</select></td>
       <input type="hidden" name="itemID[]" value="{$silo.itemID}">
  </tr>
{/foreach}
 <tr>
  <td colspan="4" align="center"><hr><input type="submit" value="{$language.assign}"  /></td>
 </tr>
</form>
 </tbody>
</table>

</div>

{include file="footer.tpl"}    