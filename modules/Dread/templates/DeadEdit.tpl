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
	<li><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	<li id="selected"><a href="">Edit</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}

<br />
<div>

<table width="900" cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
<thead>
 <tr bgcolor="#000000">
  <td colspan="5" align="center"><b>Dread {$language.edit}</b></td>
 </tr>
 <tr bgcolor="#000000">
  <td align="center"><b>Name</b></td>
  <td align="center"><b>{$language.status}</b></td>
  <td align="center"><b>{$language.comment}</b></td>
  <td align="center"><b>KB-Link</b></td>
 </tr> 
</thead>
<tbody>
<form method="post" action="{$url_dowork}">
<input type="hidden" name="module" value="Dread" />
<input type="hidden" name="action" value="editdeaddread" />
<input type="hidden" name="dread[id]" value="{$dread.Id}" />
 <tr>
  <td align="center">{$dread.name}</td>{assign var=s value=$dread.status}
  <td align="center"><font color="#{$color[$dread.status]}">{$language.$s}</font></td>
  <td align="center"><input name="dread[text]" type="text" value="{$dread.bemerkung}" size="40" /></td>
  <td align="center"><input name="dread[kblink]" type="text" value="{$dread.kblink}" size="50" /></td>
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