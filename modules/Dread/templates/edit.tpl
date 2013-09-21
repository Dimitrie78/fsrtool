{* Smarty *}
{************ Dread.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('form#dreadsel a').click(function(){
		var dread = $(this).attr('href').replace(/#/g,'');
		$('form#dreadsel input').attr('value',dread);
		$('form#dreadsel').submit();
	}).attr('title','Details/Ausgabe');
});
</script>{/literal}

<div id="title">&raquo; Dreadtool</div>
<div id="menu">
<ul class="items">
	<li><a href="{$url_index}?module=Dread&action=main">{$language.overview}</a></li>
	<li id="selected"><a href="">Edit</a></li>
	<li><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}

<br />
<div>

<table width="700" cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
<thead>
 <tr bgcolor="#000000">
  <td colspan="5" align="center"><b>Dread {$language.edit}</b></td>
 </tr>
 <tr bgcolor="#000000">
  <td align="center"><b>Name</b></td>
  <td align="center"><b>{$language.location}</b></td>
  <td align="center"><b>{$language.status}</b></td>
  <td align="center"><b>{$language.insured_up_to}</b></td>
  <td align="center"><b>{$language.comment}</b></td>
 </tr> 
</thead>
<tbody>
<form method="post" action="{$url_dowork}">
<input type="hidden" name="module" value="Dread" />
<input type="hidden" name="action" value="editdread" />
<input type="hidden" name="dread[id]" value="{$dread.Id}" />
 <tr>
  <td align="center"><input name="dread[name]" type="text" value="{$dread.name|escape:"htmlall"}" size="25" /></td>
  <td align="center"><select name="dread[ort]">{html_options options=$standort selected=$dread.standort|escape:"htmlall"}</select></td>
  <td align="center"><select name="dread[stat]">{html_options options=$status selected=$dread.status|escape:"htmlall"}</select></td>
  <td align="center"><input name="dread[time]" type="text" value="{$dread.versichert_bis|date_format:"%d.%m.%Y %H:%M"}" size="20" /></td>
  <td align="center"><input name="dread[text]" type="text" value="{$dread.bemerkung|escape:"htmlall"}" size="40" /></td>
 </tr>
 <tr>
  <td colspan="5" align="center"><input type="submit" value="{$language.accept}" /></td>
 </tr>
</form>

</tbody>
</table>

</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}