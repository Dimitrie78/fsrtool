{include file="header.tpl"}    
{include file="file:[eveorder]menu.tpl"}

<script src="classes/jqry_plugins/highcharts.js" type="text/javascript"></script>

<div id="piechart" style="width:700px;margin:20px;"></div>

<script type="text/javascript">
{literal}
var chart1; // globally available
$(document).ready(function() {
	chart1 = new Highcharts.Chart({
		chart: {
			renderTo: 'piechart',
			defaultSeriesType: 'pie',
			margin: [50, 400, 50, 0],
			backgroundColor: 'none',
		},
		title: {
			text: unescape('Bestellstatistik <i>(Items %FCber 1% Bestellvolumen)</i>'),
			style: {
				color: '#fff'
			}
		},
		tooltip: {
			formatter: function() {
				return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					formatter: function() {
						if (this.y > 10) return this.point.name;
					},
					color: '#fff',
					style: {
						font: '13px Trebuchet MS, Verdana, sans-serif'
					}
				}
			}
		},
		legend: {
			layout: 'vertical',
			backgroundColor: '#fff',
			style: {
				left: 'auto',
				bottom: 'auto',
				right: '10px',
				top: '30px'
			}
		},
		credits: {
			enabled: false
		},
		series: [{
			type: 'pie',
			data: [
{/literal}
{assign var='other' value=0}
{foreach from=$stats item=this}
{if $this.price > 0}
{math equation='(x/y)*100' x=$this.price y=$summe assign='pc'}{if $pc < 1}{math equation='x+y' assign='other' x=$other y=$pc}{else}
["{$this.name} ({$this.quantity} Stk)",{$pc|round:2}],
{/if}
{/if}
{/foreach}['Andere',   {$other|round:2}]
{literal}
			]
		}]
	});
});
{/literal}
</script>

<table class="data" cellpadding="3" cellspacing="0" style="width:600px">
  <thead>
    <tr>
	  <td>Date</td>
	  <td align="right">User</td>
	  <td align="right">Corp</td>
	</tr>
  </thead>
  <tbody>
  {foreach $stat as $keyvar=>$itemvar}  
	<tr>
	  <td>{$keyvar}</td>
	  <td align="right">{$itemvar.user.price|number_format:2:',':'.'}</td>
	  <td align="right">{$itemvar.corp.price|number_format:2:',':'.'}</td>
	</tr>
  {/foreach}
  </tbody>
</table>
<br />
<table class="data" cellpadding="3" cellspacing="0" style="width:600px">
  <thead>
    <tr>
	  <td>Name
		{if $sort == "2"}    <a href="{$index}&amp;action=stats&amp;sort=1"><img src="icons/sort_desc.png" alt="sort" /></a>
		{elseif $sort == "1"}<a href="{$index}&amp;action=stats&amp;sort=2"><img src="icons/sort_asc.png" alt="sort" /></a>
		{else}			     <a href="{$index}&amp;action=stats&amp;sort=1"><img src="icons/sort.png" alt="sort" /></a>
		{/if}</td>
	  <td align="right">Quantity
		{if $sort == "4"}    <a href="{$index}&amp;action=stats&amp;sort=3"><img src="icons/sort_desc.png" alt="sort" /></a>
		{elseif $sort == "3"}<a href="{$index}&amp;action=stats&amp;sort=4"><img src="icons/sort_asc.png" alt="sort" /></a>
		{else}			     <a href="{$index}&amp;action=stats&amp;sort=4"><img src="icons/sort.png" alt="sort" /></a>
		{/if}</td>
	  <td align="right">{$language.whole_price}</td>
	</tr>
  </thead>
  <tbody>
  {foreach from=$stats item=this}  
	<tr>
	  <td>{$this.name}</td>
	  <td align="right">{$this.quantity}</td>
	  <td align="right">{$this.price|number_format:2:',':'.'}</td>
	</tr>
  {/foreach}
  </tbody>
  <tfoot>
    <tr>
	  <td colspan="2">{$language.sum_of_all_orders}:</td>
	  <td align="right">{$summe|number_format:2:',':'.'}</td>
	</tr>
  </tfoot>
</table>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}