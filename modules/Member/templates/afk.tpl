<table class="snow" cellpadding="3" cellspacing="0" style="width: 1000px">
 <thead>
 <tr>
  <td width="12%">Name:</td>
  <td width="13%">abwesend von:</td>
  <td width="13%">abwesend bis:</td>
  <td>Grund:</td>  
 </tr>
</thead>
<tbody>
{foreach from=$afk item=thisafk}
 <tr bgcolor="#ffa9a9">
  <td>{$thisafk.charName}</td>
  <td align="center">{$thisafk.date_go|date_format:"%d.%m.%Y %H:%M"}</td>
  <td align="center">{if ($thisafk.date_back != "0")}{$thisafk.date_back|date_format:"%d.%m.%Y %H:%M"}{else}---{/if}</td>
  <td>{$thisafk.afk_text}</td>  
 </tr>
{/foreach}
</tbody>
</table>