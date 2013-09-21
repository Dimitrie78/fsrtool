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

<p>Snowflake last updated: {$updateTime|date_format:"%d.%m.%Y - %H:%M:%S"}</p>


<table class="snow" cellpadding="3" cellspacing="0" style="width: 1000px">
 <thead> 
  <tr>
    <td>There are {$numMain} mains and {$numAlt} alts.</td>
	<td style='background-color:#b9f0b9;color:black; width:30%'>< 30d in corp</td>
  <tr>
  </tr>
	<td>Of that, {$numRecent} mains have logged in within the past 72 hours.</td>
	<td style='background-color:#ffa9a9;color:black; width:30%'>> 30d with no login</td>
  <tr>
  </tr>
	<td>Of that, {$numNew} mains are new.</td>
	<td style='background-color:#52c8f2;color:black; width:30%'>25-35d in corp</td>
  <tr>
  </tr>
	<td>Of that, {$numInactive} mains are inactive.</td>
	<td style='background-color:#fdff5e;color:black; width:30%'>15-30d with no login</td>
  </tr>
  <tr class="headcol">
    <td style="width:70%">Name</td>
	<td style="width:30%; text-align:center">Last Seen</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$members item=this}
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
    <td>{renderNameNew char=$this}</td>
		{assign var=last value=$this.lastSeen}
	<td>{assign var=timePassed value=$eveTime-$last}
		{if ($timePassed < 3600)} - {($timePassed/2)|number_format:2:',':'.'} mins
		{elseif ($timePassed < 172800)} - {($timePassed/3600)|number_format:2:',':'.'} hrs
		{else} - {($timePassed/86400)|number_format:2:',':'.'} days {/if}
	</td>
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