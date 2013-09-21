{* Smarty *}
{************ Dread.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; Dreadtool</div>
<div id="menu">
<ul class="items">
	<li><a href="{$url_index}?module=Dread&action=main">{$language.overview}</a></li>
	<li><a href="{$url_index}?module=Dread&action=ausgabe">{$language.distribution}</a></li>
	<li id="selected"><a href="{$url_index}?module=Dread&action=settings">Settings</a></li>
	<li><a href="{$url_index}?module=Dread&action=tot">{$language.elephant_graveyard}</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('table.skill tbody tr').hover(function(){
		$(this).css('background-color','#467c15');
	},
	function(){
		$(this).css('background-color','');
	});
});
</script>{/literal}

{include file="file:[Dread]add.tpl"}

<div align="center">
<h4>Skill {$language.requirements}</h4>
<table>
<tr>
  <td valign="top" align="center" colspan="4">
	<table class="skill" width="250" cellpadding="0" cellspacing="0" style="border: 2px #000 solid;">
	  <thead><tr bgcolor="#000000">
		<td colspan="3" align="center"><b>{$language.basic_skills}</b></td>
	  </tr></thead><tbody>
	{foreach from=$skills item=thisSkill}
	 {if ($thisSkill.ship_id == "0")}
	  <tr bgcolor="{cycle name="Skills" values="#444444,#333333"}">
		<td style="padding-left:5px;">{$thisSkill.typeName}</td>
		<td>{$thisSkill.quantity}</td>
		<td><a href="{$url_dowork_del}&amp;shipid={$thisSkill.ship_id}&amp;skillid={$thisSkill.skill_id}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	  </tr>
	 {/if}
	{/foreach}</tbody>
	</table>
  </td>
</tr>
<tr>
  <td valign="top">
	<table class="skill"  width="200" cellpadding="0" cellspacing="0" style="border: 2px #00b204 solid;">
	  <thead><tr bgcolor="#00b204">
		<td colspan="3" align="center"><b>Moros</b></td>
	  </tr></thead><tbody>
	{foreach from=$skills item=thisSkill}
	 {if ($thisSkill.ship_id == "19724")}
	  <tr bgcolor="{cycle name="Moros" values="#444444,#333333"}">
		<td style="padding-left:5px;">{$thisSkill.typeName}</td>
		<td>{$thisSkill.quantity}</td>
		<td><a href="{$url_dowork_del}&amp;shipid={$thisSkill.ship_id}&amp;skillid={$thisSkill.skill_id}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	  </tr>
	 {/if}
	{/foreach}</tbody>
	</table>
  </td>
  <td valign="top">
	<table class="skill"  width="200" cellpadding="0" cellspacing="0" style="border: 2px #b20000 solid;">
	  <thead><tr bgcolor="#b20000">
		<td colspan="3" align="center"><b>Naglfar</b></td>
	  </tr></thead><tbody>
	{foreach from=$skills item=thisSkill}
	 {if ($thisSkill.ship_id == "19722")}
	  <tr bgcolor="{cycle name="Naglfar" values="#444444,#333333"}">
		<td style="padding-left:5px;">{$thisSkill.typeName}</td>
		<td>{$thisSkill.quantity}</td>
		<td><a href="{$url_dowork_del}&amp;shipid={$thisSkill.ship_id}&amp;skillid={$thisSkill.skill_id}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	  </tr>
	 {/if}
	{/foreach}</tbody>
	</table>
  </td>
  <td valign="top">
	<table class="skill"  width="200" cellpadding="0" cellspacing="0" style="border: 2px #b2a800 solid;">
	  <thead><tr bgcolor="#b2a800">
		<td colspan="3" align="center"><b>Revelation</b></td>
	  </tr></thead><tbody>
	{foreach from=$skills item=thisSkill}
	 {if ($thisSkill.ship_id == "19720")}
	  <tr bgcolor="{cycle name="Revelation" values="#444444,#333333"}">
		<td style="padding-left:5px;">{$thisSkill.typeName}</td>
		<td>{$thisSkill.quantity}</td>
		<td><a href="{$url_dowork_del}&amp;shipid={$thisSkill.ship_id}&amp;skillid={$thisSkill.skill_id}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	  </tr>
	 {/if}
	{/foreach}</tbody>
	</table>
  </td>
  <td valign="top">
	<table class="skill"  width="200" cellpadding="0" cellspacing="0" style="border: 2px #008eb2 solid;">
	  <thead><tr bgcolor="#008eb2">
		<td colspan="3" align="center"><b>Phoenix</b></td>
	  </tr></thead><tbody>
	{foreach from=$skills item=thisSkill}
	 {if ($thisSkill.ship_id == "19726")}
	  <tr bgcolor="{cycle name="Phoenix" values="#444444,#333333"}">
		<td style="padding-left:5px;">{$thisSkill.typeName}</td>
		<td>{$thisSkill.quantity}</td>
		<td><a href="{$url_dowork_del}&amp;shipid={$thisSkill.ship_id}&amp;skillid={$thisSkill.skill_id}"><img alt="delete" title="delete" src="icons/delete.png" /></a></td>
	  </tr>
	 {/if}
	{/foreach}</tbody>
	</table>
  </td>
</tr>
</table>

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('#search').autocomplete({
		minLength: 3,
		source: "dowork.php?module=Dread&action=ajaxSearch"
	});
	$('input#all').click(function(){
		$('input.dreadselect').attr('checked',$(this).attr('checked')).attr('disabled',$(this).attr('checked'));
	});
	$('input#all').attr('checked','checked').trigger('click').attr('checked','checked');
	$('input.dreadselect').click(function(){
		if($('input.dreadselect:checked').length >= 4)
			$('input#all').attr('checked','checked').trigger('click').attr('checked','checked');
	});
});
</script>{/literal}

<br />
<h4>Skill {$language.add}/{$language.change}</h4>
<table>
<form action="{$url_dowork_search}" method="post">
  <tr>
	<td colspan="4" style="padding:2px 5px;background-color:#000000;text-align:center;"><input type="checkbox" id="all"/><label for="all">{$language.basic_skills}</label></td>
  </tr>
  <tr>
    <td style="width:25%;padding:5px;background-color:#00b204;vertical-align:middle;"><input type="checkbox" id="dsel_1" class="dreadselect" value="19724" name="ship[]"/><label for="dsel_1">Moros</label></td>
    <td style="width:25%;padding:5px;background-color:#b20000;vertical-align:middle;"><input type="checkbox" id="dsel_2" class="dreadselect" value="19722" name="ship[]"/><label for="dsel_2">Naglfar</label></td>
    <td style="width:25%;padding:5px;background-color:#b2a800;vertical-align:middle;"><input type="checkbox" id="dsel_4" class="dreadselect" value="19720" name="ship[]"/><label for="dsel_4">Revelation</label></td>
    <td style="width:25%;padding:5px;background-color:#008eb2;vertical-align:middle;"><input type="checkbox" id="dsel_3" class="dreadselect" value="19726" name="ship[]"/><label for="dsel_3">Phoenix</label></td>
  </tr>
  <tr>
    <td>{$language.search}:</td>
    <td colspan="3"><input type="text" id="search" name="search" size="40"/></td>
  </tr>
  <tr>
    <td>Level:</td>
    <td colspan="3">
		<input id="level_1" type="radio" name="level" value="1" checked="checked"/><label for="level_1">1</label>
		<input id="level_2" type="radio" name="level" value="2" /><label for="level_2">2</label>
		<input id="level_3" type="radio" name="level" value="3" /><label for="level_3">3</label>
		<input id="level_4" type="radio" name="level" value="4" /><label for="level_4">4</label>
		<input id="level_5" type="radio" name="level" value="5" /><label for="level_5">5</label>
	</td>
  </tr>
  <tr>
    <td colspan="5" align="center"><input type="submit" value="Add/Change" /></td>
  </tr>
</form>
</table>

</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}