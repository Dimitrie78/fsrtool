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

<table class="snow" cellpadding="3" cellspacing="0" style="width: 1000px">
 <thead> 
  <tr class="headcol">
    <td colspan="2">
	  <ul class="item">
		<li{if $stat == "1"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=1">Dreadnoughts</a></li>
		<li{if $stat == "2"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=2">Carriers</a></li>
		<li{if $stat == "3"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=3">POS Gunners</a></li>
		<li{if $stat == "4"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=4">TZ Euro</a></li>
		<li{if $stat == "5"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=5">TZ American</a></li>
		<li{if $stat == "6"} id="selected"{/if}><a href="{$index}&amp;action=stats&amp;stat=6">TZ Oceanic</a></li>
	  </ul>
	</td>
  </tr>
  <tr class="headcol">
    <td colspan="2" align="center" style="font-weight:normal;">
	  {if $stat == "1"} There are {$numStats} dreadnoughts.{/if}
	  {if $stat == "2"} There are {$numStats} carriers.{/if}
	  {if $stat == "3"} There are {$numStats} POS gunners.{/if}
	  {if $stat == "4"} There are {$numStats} European TZ players.{/if}
	  {if $stat == "5"} There are {$numStats} American TZ players.{/if}
	  {if $stat == "6"} There are {$numStats} Oceanic TZ players.{/if}
	</td>
  </tr>
{if $stat == "1"} 
{include file="file:[Member]dread.tpl"}
{elseif $stat == "2"}
{include file="file:[Member]carrier.tpl"}
{elseif $stat == "3"}
{include file="file:[Member]posGunner.tpl"}
{else}
{include file="file:[Member]lastSeen.tpl"}
{/if}
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