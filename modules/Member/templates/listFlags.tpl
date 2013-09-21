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
		<li{if $flag == "1"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=1">Inactive</a></li>
		<li{if $flag == "2"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=2">Alt No Main</a></li>
		<li{if $flag == "3"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=3">AFK</a></li>
		<li{if $flag == "4"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=4">Notes</a></li>
		<li{if $flag == "5"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=5">Investigate</a></li>
		<li{if $flag == "6"} id="selected"{/if}><a href="{$index}&amp;action=flags&amp;flag=6">Probation</a></li>
	  </ul>
	</td>
  </tr>
  <tr class="headcol">
    <td colspan="2" align="center" style="font-weight:normal;">
	  {if $flag == "1"} There are {$numFlag} mains inactive.{/if}
	  {if $flag == "2"} There are {$numFlag} alts in corp without mains.{/if}
	  {if $flag == "3"} There are {$numFlag} mains AFK.{/if}
	  {if $flag == "4"} There are {$numFlag} notes.{/if}
	  {if $flag == "5"} There are {$numFlag} under investigation.{/if}
	  {if $flag == "6"} There are {$numFlag} member(s) on probation.{/if}
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