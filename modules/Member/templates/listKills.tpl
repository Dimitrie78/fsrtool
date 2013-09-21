{* Smarty *}
{include file="header.tpl"}    

<div id="title">&raquo; Membertool</div>
<div id="pos">
<form action="{$url_index_module}" method="post" />
  <input type="hidden" name="module" value="Member" />
  <input type="hidden" name="action" value="showChar" />
  <input type="text" name="charID" id="charSearch" />
  <input type="submit" value="Search" />
</form>
</div>
{include file="file:[Member]bar.tpl"}
</div> {* end of div started in header.tpl *}

<script type="text/javascript" src="modules/Member/inc/snowflake.js"></script>

<br />

{if $curUser->corpID == 147849586}
{assign var="pvpDiv" value="FSR Rangers"}
{assign var="pDiv" value="FSR Rangers"}
{else}
{assign var="pvpDiv" value="PVP"}
{assign var="pDiv" value="PvP member."}
{/if}

<table class="snow" cellpadding="3" cellspacing="0" style="width: 1000px">
 <thead> 
  <tr class="headcol">
    <td colspan="7">
	  <ul class="item">
		<li{if $state == "0"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=0">Probation</a></li>
		<li{if $state == "1"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=1">{$pvpDiv}</a></li>
		<li{if $state == "2"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=2">Mining</a></li>
		<li{if $state == "3"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=3">POS</a></li>
		<li{if $state == "4"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=4">Support</a></li>
		<li{if $state == "5"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=5">High Command</a></li>
		<li{if $state == "6"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=6">Leader</a></li>
{if     $curUser->corpID == 147849586}<li{if $state == "7"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=7">FSR Urgestein</a></li>
{elseif $curUser->corpID == 144965822}<li{if $state == "7"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=7">OI Legend</a></li>
{else}<li{if $state == "7"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=7">Legend</a></li>{/if}
		<li{if $state == "8"} id="selected"{/if}><a href="{$index}&amp;action=kill&amp;state=8">All Members</a></li>
	  </ul>
	</td>
  </tr>
  <tr>
    <td style="width:30%">Name <span style=" float:right; text-align:right">Kills in:</span></td>
	<td style="text-align:center">{$months[5]}<br />Kills / Losses</td>
	<td style="text-align:center">{$months[4]}<br />Kills / Losses</td>
	<td style="text-align:center">{$months[3]}<br />Kills / Losses</td>
	<td style="text-align:center">{$months[2]}<br />Kills / Losses</td>
	<td style="text-align:center">{$months[1]}<br />Kills / Losses</td>
	<td style="text-align:center">{$months[0]}<br />Kills / Losses</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$kills item=char}
{if (($eveTime-$char.joined) > 60*60*24*23) and (($eveTime-$char.joined) < 60*60*24*32)}
  <tr bgcolor="#52c8f2">
{elseif ($char.inactive == "1")}
  <tr bgcolor="#ffa9a9">
{elseif (($eveTime-$char.lastSeen) > 60*60*24*15)}
  <tr bgcolor="#fdff5e">
{elseif (($eveTime-$char.joined) < 60*60*24*30)}
  <tr bgcolor="#b9f0b9">
{else}
  <tr bgcolor="#ffffff">
{/if}
    {*<td>{renderName charID=$char.charID}</td>*}
	<td>{renderNameNew char=$char}</td>
	<td style="text-align:center">{$char.kill[5].kills} / {$char.kill[5].loss}</td>
	<td style="text-align:center">{$char.kill[4].kills} / {$char.kill[4].loss}</td>
	<td style="text-align:center">{$char.kill[3].kills} / {$char.kill[3].loss}</td>
	<td style="text-align:center">{$char.kill[2].kills} / {$char.kill[2].loss}</td>
	<td style="text-align:center">{$char.kill[1].kills} / {$char.kill[1].loss}</td>
	<td style="text-align:center">{$char.kill[0].kills} / {$char.kill[0].loss}</td>
  </tr>
{/foreach}
 </tbody>
</table>

<div id="isAltWin" style="display: none;"></div>
<div id="flagWin" style="display: none;"></div>
{literal}<script type="text/javascript">
$(document).ready(function(){
	$('#charSearch').autocomplete({
		minLength: 3,
		source: "dowork.php?module=Member&action=charSearch"
	});
});
</script>{/literal}
{include file="footer.tpl"}