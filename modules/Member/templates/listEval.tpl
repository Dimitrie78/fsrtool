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
    <td colspan="3">
	  <ul class="item">
		<li{if $eva == "1"} id="selected"{/if}><a href="{$index}&amp;action=eval&amp;eva=1">{$pvpDiv}</a></li>
		<li{if $eva == "2"} id="selected"{/if}><a href="{$index}&amp;action=eval&amp;eva=2">Mining</a></li>
		<li{if $eva == "3"} id="selected"{/if}><a href="{$index}&amp;action=eval&amp;eva=3">POS</a></li>
		<li{if $eva == "4"} id="selected"{/if}><a href="{$index}&amp;action=eval&amp;eva=4">Support</a></li>
		<li{if $eva == "5"} id="selected"{/if}><a href="{$index}&amp;action=eval&amp;eva=5">none</a></li>
	  </ul>
	</td>
  </tr>
  <tr class="headcol">
    <td colspan="3" align="center" style="font-weight:normal;">
	  {if $eva == "1"} There are {$numEval} {$pDiv}{/if}
	  {if $eva == "2"} There are {$numEval} Mining member.{/if}
	  {if $eva == "3"} There are {$numEval} POS member.{/if}
	  {if $eva == "4"} There are {$numEval} Support member.{/if}
	  {if $eva == "5"} There are {$numEval} Mains without an division.{/if}
	</td>
  </tr>
{include file="file:[Member]eval.tpl"}
 </tbody>
</table>

<div id="isAltWin" style="display: none;"></div>
<div id="editEvalWin" style="display: none;"></div>
<div id="addEvalWin" style="display: none;"></div>
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