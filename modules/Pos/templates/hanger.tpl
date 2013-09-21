{literal}
<script type="text/javascript">
$(document).ready(function(){

	//Hide (Collapse) the toggle containers on load
	//$('div.box').hide(); 

	//Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
	$('div.hanger').click(function(){
		$(this).next().slideToggle(200);
	});

});
</script>
{/literal}
{if count($CorpHangers) >= 1}
{foreach from=$CorpHangers key=key item=thisH}

<div class="hanger" id="hanger">{$thisH.location}</div>
<div class="box"{if $selCorpSAG[$key]}{else} style="display:none"{/if}>
<table class="data" cellpadding="2" cellspacing="1" style="border:solid 1px #000">
 <thead>
  <tr class="headcol" style="font-weight:normal;background-color:#4F0202;">
   <td align="center">&nbsp;</td>
   <td align="center">Amarr Fuel Blocks</td>
   <td align="center">Caldari Fuel Blocks</td>
   <td align="center">Gallente Fuel Blocks</td>
   <td align="center">Minmatar Fuel Blocks</td>
   <td align="center">Stront</td>
   <td align="center">&nbsp;</td>
  </tr>
 </thead>
 <tbody>
  <tr bgcolor="#222222">
   <td>1 er</td>
   <td align="right">{if ($thisH.4.4247)}{$thisH.4.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.4.4051)}{$thisH.4.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.4.4312)}{$thisH.4.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.4.4246)}{$thisH.4.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.4.16275)}{$thisH.4.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][4]" type="checkbox" {if $selCorpSAG[$key][4]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#333333">
   <td>2 er</td>
   <td align="right">{if ($thisH.116.4247)}{$thisH.116.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.116.4051)}{$thisH.116.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.116.4312)}{$thisH.116.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.116.4246)}{$thisH.116.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.116.16275)}{$thisH.116.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][116]" type="checkbox" {if $selCorpSAG[$key][116]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#222222">
   <td>3 er</td>
   <td align="right">{if ($thisH.117.4247)}{$thisH.117.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.117.4051)}{$thisH.117.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.117.4312)}{$thisH.117.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.117.4246)}{$thisH.117.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.117.16275)}{$thisH.117.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][117]" type="checkbox" {if $selCorpSAG[$key][117]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#333333">
   <td>4 er</td>
   <td align="right">{if ($thisH.118.4247)}{$thisH.118.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.118.4051)}{$thisH.118.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.118.4312)}{$thisH.118.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.118.4246)}{$thisH.118.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.118.16275)}{$thisH.118.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][118]" type="checkbox" {if $selCorpSAG[$key][118]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#222222">
   <td>5 er</td>
   <td align="right">{if ($thisH.119.4247)}{$thisH.119.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.119.4051)}{$thisH.119.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.119.4312)}{$thisH.119.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.119.4246)}{$thisH.119.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.119.16275)}{$thisH.119.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][119]" type="checkbox" {if $selCorpSAG[$key][119]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#333333">
   <td>6 er</td>
   <td align="right">{if ($thisH.120.4247)}{$thisH.120.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.120.4051)}{$thisH.120.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.120.4312)}{$thisH.120.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.120.4246)}{$thisH.120.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.120.16275)}{$thisH.120.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][120]" type="checkbox" {if $selCorpSAG[$key][120]}checked="checked"{/if}/></td>
  </tr>
  <tr bgcolor="#222222">
   <td>7 er</td>
   <td align="right">{if ($thisH.121.4247)}{$thisH.121.4247|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.121.4051)}{$thisH.121.4051|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.121.4312)}{$thisH.121.4312|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.121.4246)}{$thisH.121.4246|number_format}{else}&nbsp;{/if}</td>
   <td align="right">{if ($thisH.121.16275)}{$thisH.121.16275|number_format}{else}&nbsp;{/if}</td>
   <td align="center"><input name="corpSAG[{$key}][121]" type="checkbox" {if $selCorpSAG[$key][121]}checked="checked"{/if}/></td>
  </tr>
 </tbody>
</table>
</div>

{/foreach}
<p><input type="submit" name="submit" value="Filter" /></p>
{/if}
