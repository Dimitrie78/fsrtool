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

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
{literal}    
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1', {'packages':['corechart']});
      
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);
      
      // Callback that creates and populates a data table, 
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {
	  
		// align tables
		jQuery('.gg_tt_chart .gg_tb').css('width','350px');
	   
{/literal}{if ($ratting == 'player')}{literal}
		  draw_by_player();
{/literal}{else if ($ratting == 'system')}{literal}
		  draw_by_system();
{/literal}{else if ($ratting == 'region')}{literal}
		  draw_by_region();
{/literal}{/if}{literal}
	

	//	  drawHours();
	//	  drawLast3Days();
		  
	//	  draw_by_agents();
		  
		 
    }
	
	function draw_by_player() {
	
		var data = new google.visualization.DataTable();
		  data.addColumn('string', 'chars');
		  data.addColumn('number', 'ratbountys');
		  
//		  data.addRow(  );
		  var rows = [];

	var rows =
		{/literal}
		{$content.json}
		{literal}
	;
		data.addRows(rows);
		  
		var formatter = new google.visualization.NumberFormat({suffix: ' ISK'});
		formatter.format(data, 1); // Apply formatter to second column

		  // Instantiate and draw our chart, passing in some options.
		  var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
		  chart.draw(data, {width: 500, height: 360});
	}
	
	function draw_by_system() {
		// 
		  // Create our data table.
		  var data = new google.visualization.DataTable();
		  data.addColumn('string', 'system');
		  data.addColumn('number', 'ratbountys');
		  
//		  data.addRow(  );
		  var rows = [];
		  
	var rows =
		{/literal}
		{$content.json}
		{literal}
	;				  
		  
		  data.addRows(rows);
		  
		var formatter = new google.visualization.NumberFormat({suffix: ' ISK'});
		formatter.format(data, 1); // Apply formatter to second column


		  // Instantiate and draw our chart, passing in some options.
		  var chart = new google.visualization.PieChart(document.getElementById('carebearing_by_system'));
		  chart.draw(data, {width: 500, height: 360});
		
	}
	
	function draw_by_region() {
		// 
		  // Create our data table.
		  var data = new google.visualization.DataTable();
		  data.addColumn('string', 'system');
		  data.addColumn('number', 'ratbountys');
		  
//		  data.addRow(  );
		  var rows = [];
		  
	var rows =
		{/literal}
		{$content.json}
		{literal}
	;
		  data.addRows(rows);
		  
		var formatter = new google.visualization.NumberFormat({suffix: ' ISK'});
		formatter.format(data, 1); // Apply formatter to second column

		  // Instantiate and draw our chart, passing in some options.
		  var chart = new google.visualization.PieChart(document.getElementById('carebearing_by_system'));
		  chart.draw(data, {width: 500, height: 360});
		
	}
		
	function drawHours() {
	  // Create and populate the data table. 
	  var data = new google.visualization.DataTable();
	  data.addColumn('string', 'period');
	  data.addColumn('number', 'carebearing');
	  
	data.addRow( [ "00:00-00:59", parseFloat('0') ] );
	data.addRow( [ "01:00-01:59", parseFloat('0') ] );
	data.addRow( [ "02:00-02:59", parseFloat('0') ] );
	data.addRow( [ "03:00-03:59", parseFloat('0') ] );
	data.addRow( [ "04:00-04:59", parseFloat('0') ] );
	data.addRow( [ "05:00-05:59", parseFloat('0') ] );
	data.addRow( [ "06:00-06:59", parseFloat('0') ] );
	data.addRow( [ "07:00-07:59", parseFloat('0') ] );
	data.addRow( [ "08:00-08:59", parseFloat('0') ] );
	data.addRow( [ "09:00-09:59", parseFloat('0') ] );
	data.addRow( [ "10:00-10:59", parseFloat('1502250') ] );
	data.addRow( [ "11:00-11:59", parseFloat('0') ] );
	data.addRow( [ "12:00-12:59", parseFloat('2158625') ] );
	data.addRow( [ "13:00-13:59", parseFloat('890000') ] );
	data.addRow( [ "14:00-14:59", parseFloat('8310182') ] );
	data.addRow( [ "15:00-15:59", parseFloat('303355733') ] );
	data.addRow( [ "16:00-16:59", parseFloat('2017876') ] );
	data.addRow( [ "17:00-17:59", parseFloat('101608318') ] );
	data.addRow( [ "18:00-18:59", parseFloat('404645882') ] );
	data.addRow( [ "19:00-19:59", parseFloat('64004214') ] );
	data.addRow( [ "20:00-20:59", parseFloat('26487129') ] );
	data.addRow( [ "21:00-21:59", parseFloat('31042178') ] );
	data.addRow( [ "22:00-22:59", parseFloat('122986233') ] );
	data.addRow( [ "23:00-23:59", parseFloat('0') ] );

		var formatter = new google.visualization.NumberFormat({suffix: ' ISK'});
		formatter.format(data, 1); // Apply formatter to second column
	 
	  // Create and draw the visualization.
	  new google.visualization.LineChart(document.getElementById('carebearing_by_hour')).
		  draw(data, {curveType: "function",
					  width: 600, height: 500,
					  vAxis: {maxValue: 10}}
			  );
	}
	
	function drawLast3Days() {
			}
	
	function draw_by_agents() {
		//	<div id="carebearing_by_agent_missions"></div>
		//	<div id="carebearing_by_agent_income"></div>
		
		// 
	  // Create and populate the data table. 
	  var data = new google.visualization.DataTable();
	  data.addColumn('string', 'agent');
	  data.addColumn('number', 'carebearing');
	  
	data.addRow( [ "Mesghoh Huchmib(lv3), Pimebeka", parseFloat('170722000') ] );
	data.addRow( [ "Sihsad Aphuka(lv4), Aphend", parseFloat('13151496') ] );
	data.addRow( [ "Urara Oshuhad(lv1), Tash-Murkon Prime", parseFloat('462000') ] );

		var formatter = new google.visualization.NumberFormat({suffix: ' ISK'});
		formatter.format(data, 1); // Apply formatter to second column
	 
	  // Instantiate and draw our chart, passing in some options.
	  var chart = new google.visualization.PieChart(document.getElementById('carebearing_by_agent_income'));
	  chart.draw(data, {width: 500, height: 320});
	  
	  // Create and populate the data table. 
	  var data2 = new google.visualization.DataTable();
	  data2.addColumn('string', 'agent');
	  data2.addColumn('number', 'missions');
	  
		data2.addRow( [ "Mesghoh Huchmib(lv3), Pimebeka", parseInt('40') ] );
		data2.addRow( [ "Sihsad Aphuka(lv4), Aphend", parseInt('12') ] );
		data2.addRow( [ "Urara Oshuhad(lv1), Tash-Murkon Prime", parseInt('2') ] );

		var formatter2 = new google.visualization.NumberFormat({suffix: ' missions',fractionDigits: 0});
		formatter2.format(data2, 1); // Apply formatter to second column
	 
	  // Instantiate and draw our chart, passing in some options.
	  var chart2 = new google.visualization.PieChart(document.getElementById('carebearing_by_agent_missions'));
	  chart2.draw(data2, {width: 500, height: 320});
	
	
	}

{/literal}	
    </script>

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
<table class="gg_tt_chart">
<tr><td valign="top">
<table id="player_{$content.sort}" class="snow" cellpadding="3" cellspacing="0" style="width: 500px">
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
</td>
	<td valign="top" class="gg_chart">
		<div id="chart_div"></div>
	</td>
	</tr>
</table>

{else}
<table class="gg_tt_chart">
<tr><td valign="top">
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
</td>
	<td valign="top" class="gg_chart">
		<div id="carebearing_by_system"></div>
	</td>
	</tr>
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