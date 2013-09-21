{* Smarty *}
{include file="header.tpl"}    

<div id="title">&raquo; Membertool</div>
<div id="pos">
<form action="{$index}" method="post" />
  <input type="hidden" name="module" value="Member" />
  <input type="hidden" name="action" value="showChar" />
  <input type="text" name="charID" id="charSearch" />
  <input type="submit" value="Search" />
</form>
</div>
{include file="file:[Member]bar.tpl"}
</div> {* end of div started in header.tpl *}

<script type="text/javascript" src="modules/Member/inc/snowflake.js"></script>
<script src="classes/jqry_plugins/highcharts.js" type="text/javascript"></script>


{if $charts}
<script type="text/javascript">
{literal}
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'container', 
         defaultSeriesType: 'area'
      },
      title: {
         text: 'Carebear Stats from {/literal}{$curUser->allyname}{literal}'
      },
      xAxis: {
		  title: {
            text: 'Days ago'
          },
		  categories: ['7', '6', '5', '4', '3', '2', '1', '0']
      },
      yAxis: {
         title: {
            text: 'Isk'
         },
         labels: {
            formatter: function() {
               return this.value / 1000000 +'m';
            }
         }
      },
      tooltip: {
         formatter: function() {
            return this.series.name +' produced <b>'+
               Highcharts.numberFormat(this.y, 0, null, ' ') +'</b> Isk '+ this.x +' days ago';
         }
      },
      series: {/literal} {$charts} {literal}
   });
   
   
});
{/literal}
</script>
{/if}
<br />
	{if $period == 'alltime'}<span style="color:red;"><b>all time</b></span> |{else}<a href="{$index}&amp;action=carebears&ratting={$ratting}&period=alltime{if $char}&char={$char|urlencode}{/if}">all time</a> |{/if}
	{if $period == 'last30days'}<span style="color:red;"><b>last 30 days</b></span> |{else}<a href="{$index}&amp;action=carebears&ratting={$ratting}&period=last30days{if $char}&char={$char|urlencode}{/if}">last 30 days</a> |{/if}
	{if $period == 'last7days'}<span style="color:red;"><b>last 7 days</b></span> |{else}<a href="{$index}&amp;action=carebears&ratting={$ratting}&period=last7days{if $char}&char={$char|urlencode}{/if}">last 7 days</a> |{/if}
	{if $period == 'last24h'}<span style="color:red;"><b>last 24 hours</b></span>{else}<a href="{$index}&amp;action=carebears&ratting={$ratting}&period=last24h{if $char}&char={$char|urlencode}{/if}">last 24 hours</a>{/if}
	<hr>
	{if $Ratter->date_filter_res.mindate}
	<p>payments range from <b>{$Ratter->date_filter_res.mindate}</b> to <b>{$Ratter->date_filter_res.maxdate}</b></p>
	{/if}
	
	{if $char}
	{if $ratting == 'system'}<span style="color:red;"><b>by system</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=system&char={$char|urlencode}">by system</a> | {/if}
	{if $ratting == 'region'}<span style="color:red;"><b>by region</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=region&char={$char|urlencode}">by region</a> | {/if}
	{if $ratting == 'day'}<span style="color:red;"><b>by day</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=day&char={$char|urlencode}">by day</a> | {/if}
	{if $ratting == 'fancy'}<span style="color:red;"><b>"fancy" rats kills</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=fancy&char={$char|urlencode}">"fancy" rats kills</a> | {/if}
	{if $ratting == 'missionagents'}<span style="color:red;"><b>mission rewards by agents</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=missionagents&char={$char|urlencode}">mission rewards by agents</a> | {/if}
	{if $ratting == 'npc'}<span style="color:red;"><b>by npc</b></span>{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=npc&char={$char|urlencode}">by npc</a> {/if}
	<p>total carebearing for <b><a href="{$index}&amp;action=carebears&period={$period}">{$char}</a></b>: <span style="color:red;"><b>{$Ratter->total_ratting_selected_filter|number_format:0:',':'.'}</b></span> ISK ( and corp part of it was <span style="color:red;"><b>{$Ratter->total_ratting_selected_filter_corptax|number_format:0:',':'.'}</b></span> ISK ) </p>
	{else}
	{if $ratting == 'player'}<span style="color:red;"><b>by player</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=player">by player</a> | {/if}
	{if $ratting == 'system'}<span style="color:red;"><b>by system</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=system">by system</a> | {/if}
	{if $ratting == 'region'}<span style="color:red;"><b>by region</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=region">by region</a> | {/if}
	{if $ratting == 'highsec'}<span style="color:red;"><b>high sec only by player</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=highsec">high sec only by player</a> | {/if}
	{if $ratting == 'fancy'}<span style="color:red;"><b>"fancy" rats kills</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=fancy">"fancy" rats kills</a> | {/if}
	{if $ratting == 'missionplayer'}<span style="color:red;"><b>mission rewards by player</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=missionplayer">mission rewards by player</a> | {/if}
	{if $ratting == 'missionagents'}<span style="color:red;"><b>mission rewards by agents</b></span> |{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=missionagents">mission rewards by agents</a> | {/if}
	{if $ratting == 'npc'}<span style="color:red;"><b>by npc</b></span>{else}<a href="{$index}&amp;action=carebears&period={$period}&ratting=npc">by npc</a> {/if}
	<p>total carebearing for selected filter: <span style="color:red;"><b>{$Ratter->total_ratting_selected_filter|number_format:0:',':'.'}</b></span> ISK ( and corp part of it was <span style="color:red;"><b>{$Ratter->total_ratting_selected_filter_corptax|number_format:0:',':'.'}</b></span> ISK ) </p>
	{/if}	
	

{if ($ratting == 'player') || ($ratting == 'highsec') || ($ratting == 'missionplayer')}
<table id="player_{$content.sort}" class="snow" cellpadding="3" cellspacing="0" style="width: 800px">
 <thead>
  <tr class="headcol">
{foreach from=$content.head item=head}
    <th style="text-align:center">{$head}</th>
{/foreach}
  </tr>
 </thead>
 <tbody>

{foreach from=$content.body item=char}
{if $char.ratBountys}
  <tr bgcolor="#ffffff">
    <td>{renderCarebear char=$char}</td>
	<td style="text-align:right">{$char.ratBountys|number_format:0:',':'.'}</td>
  </tr>
{/if}
{/foreach}

 </tbody>
</table>
{else}

<table id="player_{$content.sort}" class="snow" cellpadding="3" cellspacing="0" style="width: 800px">
 <thead>
  <tr class="headcol">
{foreach from=$content.head item=head}
    <th style="text-align:center">{$head}</th>
{/foreach}
  </tr>
 </thead>
 <tbody>

{foreach from=$content.body item=char}
  <tr bgcolor="#ffffff">
{foreach from=$char item=name key=k}
  {if $name == '_char'}
	<td>{foreach from=$char._chars item=thisName}{$thisName.charName}<br />{/foreach}</td>
  {elseif $k == 'ratBountys' or $k == 'ratBounty' or $k == 'max_ratBountys' or $k == 'mission_rewards'}
	<td style="text-align:right">{$name|number_format:0:',':'.'}</td>
  {elseif $k[0] == '_'}
  {else}
	<td>{$name}</td>
  {/if}
{/foreach}
  </tr>
{/foreach}

 </tbody>
</table>
{/if}

<div id="container" style="width:700px;margin:20px;"></div>

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