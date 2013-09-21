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

<h2>{$char.char.name}</h2>
{assign var=joinedPassed value=$eveTime-$char.char.joined}
{assign var=timePassed value=$eveTime-$char.char.lastSeen}
Joined {$char.char.joined|date_format:"%d.%m.%Y - %H:%M"} ({($joinedPassed/86400)|number_format:2:',':'.'} days ago)<br />
{if     ($timePassed < 3600)  } Last seen {($timePassed/60)|number_format:2:',':'.'} mins ago
{elseif ($timePassed < 172800)} Last seen {($timePassed/3600)|number_format:2:',':'.'} hrs ago
{else} 						    Last seen {($timePassed/86400)|number_format:2:',':'.'} days ago
{/if}
{if ($char.char.inactive == 1)} (Character is inactive){/if}
<br /><br />
<table class="snow" cellpadding="3" cellspacing="0" style="width: 500px">
 <thead>
  <tr>
    <td style="width:70%">Name</td>
	<td style="width:30%; text-align:center">Last Seen</td>
  </tr>
 </thead>
 <tbody>
{if (($eveTime-$char.char.joined) > 60*60*24*23) and (($eveTime-$char.char.joined) < 60*60*24*32)}
  <tr bgcolor="#52c8f2">
{elseif ($char.char.inactive == "1")}
  <tr bgcolor="#ffa9a9">
{elseif (($eveTime-$char.char.lastSeen) > 60*60*24*15)}
  <tr bgcolor="#fdff5e">
{elseif (($eveTime-$char.char.joined) < 60*60*24*30)}
  <tr bgcolor="#b9f0b9">
{else}
  <tr bgcolor="#ffffff">
{/if}
    <td>{renderName charID=$char.char.charID}</td>
		{assign var=last value=$char.char.lastSeen}
	<td>{assign var=timePassed value=$eveTime-$last}
		{if ($timePassed < 3600)} - {($timePassed/2)|number_format:2:',':'.'} mins
		{elseif ($timePassed < 172800)} - {($timePassed/3600)|number_format:2:',':'.'} hrs
		{else} - {($timePassed/86400)|number_format:2:',':'.'} days {/if}
	</td>
  </tr>
{if $char.alts}
<tr bgcolor="#CCCCCC"><td colspan="2" style="text-align:center;"><b>ALTS</b></td></tr>
{foreach from=$char.alts item=this}

{if (($eveTime-$this.joined) > 60*60*24*23) and (($eveTime-$this.joined) < 60*60*24*32)}
  <tr bgcolor="#52c8f2">
{elseif ($this.inactive == "1")}
  <tr bgcolor="#ffa9a9">
{elseif (($eveTime-$this.lastSeen) > 60*60*24*15)}
  <tr bgcolor="#fdff5e">
{elseif (($eveTime-$this.joined) < 60*60*24*30)}
  <tr bgcolor="#b9f0b9">
{else}
  <tr bgcolor="#ffffff">
{/if}
    <td>{renderName charID=$this.charID}</td>
		{assign var=last value=$this.lastSeen}
	<td>{assign var=timePassed value=$eveTime-$last}
		{if ($timePassed < 3600)} - {($timePassed/2)|number_format:2:',':'.'} mins
		{elseif ($timePassed < 172800)} - {($timePassed/3600)|number_format:2:',':'.'} hrs
		{else} - {($timePassed/86400)|number_format:2:',':'.'} days {/if}
	</td>
  </tr>
{/foreach}
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