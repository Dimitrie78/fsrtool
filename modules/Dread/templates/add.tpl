<div>
<table width="700" cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
<thead>
 <tr bgcolor="#000000">
  <td colspan="4" align="center"><b>Dread {$language.add}</b></td>
 </tr>
 <tr bgcolor="#626456">
  <td align="center"><b>Typ</b></td>
  <td align="center"><b>Name</b></td>
  <td align="center"><b>{$language.location}</b></td>
  <td align="center"><b>{$language.comment}</b></td>
 </tr> 
</thead>
<tbody>
<form method="post" action="{$url_dowork}">
<input type="hidden" name="module" value="Dread" />
<input type="hidden" name="action" value="adddread" />
 <tr>
  <td align="center"><select name="dread[typ]">{html_options options=$ships}</select></td>
  <td align="center"><input name="dread[name]" type="text" value="F.S.R.S " size="25" /></td>
  <td align="center"><select name="dread[ort]">{html_options options=$standort}</select></td>
  <td align="center"><input name="dread[text]" type="text" value="" size="40" /></td>
 </tr>
 <tr>
  <td colspan="4" align="center"><input type="submit" value="add" /></td>
 </tr>
</form>
</tbody>
</table>
</div>
<br />
<div>
<table cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
<thead>
 <tr bgcolor="#000000">
  <td colspan="2" align="center"><b>{$language.locations}</b></td>
 </tr>
</thead>
<tbody> 
 <tr>
  <td style="border: 1px #000 solid;">
	<table  class="skill" cellpadding="2" cellspacing="0">
	{foreach from=$standort item=thisOrt}
	 <tr bgcolor="{cycle values="#444444,#333333"}">
	  <td>{$thisOrt}</td>
	  <td><a href="{$url_dowork}?module=Dread&amp;action=delort&amp;ort={$thisOrt}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	 </tr>
	{/foreach}
	</table>
  </td>
  <td style="border: 1px #000 solid;">
	<table>
	<form method="post" action="{$url_dowork}">
	 <tr>
	  <td><input name="ort" type="text" /></td>
	  <td><input type="submit" value="add" /></td>
	 </tr>
	 <input type="hidden" name="module" value="Dread" />
	 <input type="hidden" name="action" value="addort" />
	</form>
	</table>
  </td>
 </tr>
</tbody> 
</table>
</div>