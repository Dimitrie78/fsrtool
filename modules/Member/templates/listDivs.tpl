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
<br/>

<script type="text/javascript" src="modules/Member/inc/snowflake.js"></script>

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
    <td colspan="2">
	  <ul class="item">
		<li{if $division == "1"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=1">{$pvpDiv}</a></li>
		<li{if $division == "2"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=2">Mining</a></li>
		<li{if $division == "3"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=3">POS</a></li>
		<li{if $division == "4"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=4">Support</a></li>
		<li{if $division == "5"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=5">High Command</a></li>
		<li{if $division == "6"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=6">Leader</a></li>
		<li{if $division == "7"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=7">none</a></li>
{if     $curUser->corpID == 147849586}<li{if $division == "8"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=8">FSR Urgestein</a></li>
{elseif $curUser->corpID == 144965822}<li{if $division == "8"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=8">OI Legend</a></li>
{else}<li{if $division == "8"} id="selected"{/if}><a href="{$index}&amp;action=div&amp;division=8">Legend</a></li>{/if}
	  </ul>
	</td>
  </tr>
  <tr class="headcol">
    <td colspan="2" align="center" style="font-weight:normal;">
	  {if $division == "1"} There are {$numDiv} {$pDiv}{/if}
	  {if $division == "2"} There are {$numDiv} Mining member.{/if}
	  {if $division == "3"} There are {$numDiv} POS member.{/if}
	  {if $division == "4"} There are {$numDiv} Support member.{/if}
	  {if $division == "5"} There are {$numDiv} High Command member.{/if}
	  {if $division == "6"} There are {$numDiv} Leader.{/if}
	  {if $division == "7"} There are {$numDiv} Mains without an division.{/if}
	  {if $division == "8"} There are {$numDiv} Legend.{/if} 
	</td>
  </tr>
{include file="file:[Member]lastSeen.tpl"}
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