{* Smarty *}
{************ ticker.tpl *************************************}

{literal}
<style type="text/css">
.container {
  margin: 0 auto;
  padding:2px;
}
.container .wrap {
  overflow: hidden;
  height:12px;
  position: relative;
}
div.stockTicker {
  font-family: Verdana, Arial, Helvetica, San-serif;
  font-size: x-small;
  margin: 0 auto;
  padding: 0;
  position: relative;
  text-align:left;
}
div.stockTicker span{
  padding:0 10px;
  border-right:solid 2px #626456;
  float:left;
}
div.stockTicker span img{
  border:none;
  padding:0;
  margin-right:5px;
  width:10px;
}
div.stockTicker span.title{
  padding-left:100px;
  padding-right:10px;
  font-weight:bold;
}
#tickerBar{
  display:none;
  position:fixed;
  bottom:0;
  width:100%;
  background-color: rgb(51, 51, 51);
  border-top:solid 2px #000;
  border-right:solid 2px #000;
}
</style>
<script type="text/javascript" src="classes/jqry_plugins/jquery.jstockticker-1.1.1.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $('#minprices_ticker').jStockTicker({interval: 45});
  var tickerBar = $('#tickerBar').fadeIn('slow');
  $('body').css('margin-bottom',tickerBar.outerHeight(true)+'px');
});
</script>
{/literal}

<div id="tickerBar">
<div id="minprices_ticker" class="stockTicker">

<span class="title"><a href="{$index}&action=MinsPreise">Mineralienpreise:</a></span>
{foreach from=$mineralprices item=mineral key=typeID}
<span><img src="icons/16_16/icon{$mineral.icon}.png"/><b>{$mineral.Name}</b> @ {$mineral.Price|commify:2:',':'.'} isk {if $old_minprices[$typeID] AND $old_minprices[$typeID].Date != $mineral.Date}{math equation="round((y-x),2)" x=$old_minprices[$typeID].Price y=$mineral.Price assign=modified}{math equation="round(((y-x)/y)*100,2)" x=$old_minprices[$typeID].Price y=$mineral.Price assign=modified_pc}{if $modified!=0}<i{if $modified>0} style="color:rgb(120,213,36)"{/if}{if $modified<0} style="color:rgb(255,47,36)"{/if}>({if $modified>0}+{/if}{$modified} isk; {if $modified>0}+{/if}{$modified_pc}%)</i>{/if}{/if}</span>
{/foreach}

</div>
</div>
