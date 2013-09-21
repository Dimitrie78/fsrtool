{* Smarty *}
{************ skills.tpl *************************************}
{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}
{assign var=title value="Mining Div Skills"}
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/menu.tpl"}
{* ------------------------------- *}

{literal}
<script type="text/javascript">
$(document).ready(function(){
	$('ul.scroller>li div').each(function(){
	  var _this = $(this);
		var id = _this.find('a').attr('href').match(/\d.*/);
		_this.bind('click',function(){
			toggleUser(id);
			return false;
		});
	});
	$('table.miningDiv tbody tr').hover(
			function(){
				$(this).css('background-color','#467c15');
			},
			function(){
				$(this).css('background-color','');
			}
	);
	scroller = function(obj){
	  $(obj).each(function(){
			var _obj = $(this);
			var w=fw=lw=0;
			if(_obj.parent('div.scroller_content').length == 0)
			  _obj.wrap('<div class="scroller_content">');
			var content = _obj.parent('div.scroller_content').css({
			  overflow: 'hidden',
				height: 'auto',
				cursor:'w-resize'
			});
			_obj.children().each(function(){
			  var _this = $(this);
			  w += _this.outerWidth(true);
			});
			fw = _obj.find('li:first-child').outerWidth(true);
			lw = _obj.find('li:last-child').outerWidth(true);
			_obj.css({
				width : w+'px'
			});
			content.mousemove(function(e){
			  // ZOMG DO NOT EDIT
				var contentWidth = Math.round((content.innerWidth(true)+content.outerWidth(true))/2);
				var contentBorderWidth = contentWidth-content.innerWidth(true);
				content.scrollLeft(Math.round((e.pageX-content.offset().left-contentBorderWidth-fw) * ((_obj.outerWidth(true)-(content.innerWidth(true)))/(contentWidth-contentBorderWidth-lw-fw))));
			});
		});
	};
	toggleUser = function(user){
		if($('div.'+user)) {
		  $('div.user').css('display','none');
			$('div.'+user).toggle('fast');
		}
	}
	scroller($('ul.scroller'));
});
</script>
<style type="text/css">
div.scroller_content{
	width:80%;
	min-width:1000px;
	overflow:auto;
	border:solid 1px gray;
	padding:0 5px;
	height:120px;
	background-color:#333;
}
ul.scroller{
	padding:0;
	margin:0;
}
ul.scroller li{
	float:left;
	margin:5px 5px 5px 0;
	list-style-type:none;
	background-color:#444;
}
ul.scroller li div:hover{
	background-color:#000;
}
ul.scroller li div{
	float:left;
	padding:5px;
}
ul.scroller li div i{
  font-size:0.8em;
}
ul.scroller li img{
	display:block;
	border: 1px solid gray;
	margin-bottom:5px;
}
</style>
{/literal}

{*<img style="border: 1px solid gray;" alt="{$name}" src="{getportrait characterID=$characterID size=256}" />*}

Mains: {$numMains} Alts: {$numAlts}
<pre>
Um die Fragen ein wenig einzud&auml;mmen:
- einige von euch haben sich mehrere eveorder Accounts gemacht. Die falschen m&uuml;ssen gel&ouml;scht werden.
- Wendet euch dazu an Max (MystD) oder Dimitrie
- Bei vielen fehlen die Alt Chars.
- Klickt oben auf *PROFIL* und tragt diese nach
	  
Danke f&uuml;r eure Kooperation.
42
</pre>

<div class="scroller_content">
<ul class="scroller">
{foreach from=$skills item=thisChar}{if $isALT[$thisChar.charID]=="main"}
	<li>
{foreach from=$hisMAIN item=mainlist key=charID}{if $mainlist==$thisChar.charName}
	  <div>
			<img alt="{$skills[$charID].charName}" src="http://image.eveonline.com/Character/{$charID}_64.jpg" />
			<a href="#{$charID}">{if $isALT[$charID]==main}<b>{$skills[$charID].charName}</b>{else}{$skills[$charID].charName}{/if}</a><br/>
			<i>({$isALT[$charID]})</i>
		</div>
{/if}{/foreach}
	</li>
{/if}{/foreach}
</ul>
</div><br style="clear:both"/>

{foreach from=$skills item=thisChar name=Char}
<div class="user {$thisChar.charID}" style="display:none ">
<table cellspacing="0" cellpadding="5" style="border: solid 1px gray;background-color: #333">
<thead>
	<tr>
		<th colspan="3" style="padding:10px">{$thisChar.charName} ({$isALT[$thisChar.charID]})
{if $hisMAIN[$thisChar.charID]<> $thisChar.charName}
			<br/><font size=-2>alt von {$hisMAIN[$thisChar.charID]}</font>
{/if}
		</th>
	</tr>
</thead>
<tbody style="">
	<tr valign="top">
  <td>
  	<table class="miningDiv" style="border: solid 1px grey" cellspacing="0" cellpadding="2" summary="Mining">
		  <thead>
				<tr style="background-color:#000;">
					<th colspan="3">Mining</th>
				</tr>
			</thead>
			<tbody>
{foreach from=$thisChar.Mining item=Mining}
{assign var='color' value=$bgcolor[$Mining.level]}
			<tr bgcolor="{cycle values="#444444,#333333"}">
				<td style="color:{$color};width:230px">{$Mining.name}</td>
				<td><img src="icons/skillsheet/level{$Mining.level}.gif" alt="{$Mining.level}"/></td>
			</tr>
{/foreach}
		</table>
  </td>

	<td>
		<table class="miningDiv" style="border: solid 1px grey" cellspacing="0" cellpadding="2" summary="Support">
		  <thead>
				<tr style="background-color:#000">
					<th colspan="3">Support</th>
				</tr>
			</thead>
			<tbody>
{if $thisChar.Hauler}{foreach from=$thisChar.Hauler item=Hauler name=h}
{assign var='color' value=$bgcolor[$Hauler.level]}
				<tr bgcolor="{cycle values="#444444,#333333"}">
					<td style="width:110px">{if $smarty.foreach.h.first}Hauler:{else}&nbsp;{/if}</td>
					<td style="color:{$color}">{$Hauler.name}</td>
					<td><img src="icons/skillsheet/level{$Hauler.level}.gif" alt="{$Hauler.level}"/></td>
				</tr>
{/foreach}
{/if}
{if $thisChar.Freighter}	
{foreach from=$thisChar.Freighter item=Freighter name=f}
{assign var='color' value=$bgcolor[$Freighter.level]}
				<tr bgcolor="{cycle values="#444444,#333333"}">
					<td style="width:110px">{if $smarty.foreach.f.first}Freighter:{else}&nbsp;{/if}</td>
					<td style="color:{$color}">{$Freighter.name}</td>
					<td><img src="icons/skillsheet/level{$Freighter.level}.gif" alt="{$Freighter.level}"/></td>
				</tr>
{/foreach}
{/if}
{if $thisChar.CommandShips}	
{foreach from=$thisChar.CommandShips item=CommandShips name=c}
{assign var='color' value=$bgcolor[$CommandShips.level]}
				<tr bgcolor="{cycle values="#444444,#333333"}">
					<td style="width:110px">{if $smarty.foreach.c.first}Command Ships:{else}&nbsp;{/if}</td>
					<td style="color:{$color}">{$CommandShips.name}</td>
					<td><img src="icons/skillsheet/level{$CommandShips.level}.gif" alt="{$CommandShips.level}"/></td>
				</tr>
{/foreach}
{/if}
{foreach from=$thisChar.Support item=Support}
{assign var='color' value=$bgcolor[$Support.level]}
				<tr bgcolor="{cycle values="#444444,#333333"}">
					<td style="color:{$color};width:230px" colspan="2">{$Support.name}</td>
					<td><img src="icons/skillsheet/level{$Support.level}.gif" alt="{$Support.level}"/></td>
				</tr>
{/foreach}
	    </tbody>
		</table>
	</td>
  
	<td>
		<table class="miningDiv" style="border: solid 1px grey" cellspacing="0" cellpadding="2" summary="Industry">
		  <thead>
				<tr style="background-color:#000">
					<th colspan="3">Industry</th>
				</tr>
			</thead>
			<tbody>
{foreach from=$thisChar.Industry item=Industry}
{assign var='color' value=$bgcolor[$Industry.level]}
				<tr bgcolor="{cycle values="#444444,#333333"}">
					<td style="color:{$color};width:230px">{$Industry.name}</td>
					<td><img src="icons/skillsheet/level{$Industry.level}.gif" alt="{$Industry.level}"/></td>
				</tr>
{/foreach}
			</tbody>
		</table>
	</td>
</tr>

</tbody>
</table>
</div>
{/foreach}

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}