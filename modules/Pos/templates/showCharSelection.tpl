{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

<div id="title">&raquo; POS</div>
<div id="menu">
<ul class="items">
	<li><a href="{$index}&action=online">online</a></li>
	<li><a href="{$index}&action=offline">offline</a></li>
	<li><a href="{$index}&action=phpBB">to phpBB</a></li>
    <li><a href="{$index}&action=fuelBill">Fuel Bill</a></li>
	<li><a href="{$index}&action=settings">settings</a></li>
    <li><a href="{$index}&action=globalTower">Global Tower</a></li>
	<li id="selected">Charakter ausw&auml;hlen</li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>
<script type="text/javascript" src="inc/reg.js"></script>

<div style="width:1000px">
<h4>Select account to register</h4>

<br />
<form action="{$url_dowork}" method="post" id="reg">
<input type="hidden" name="module" value="{$activeModule}" />
<input type="hidden" name="action" value="addCharApi" />
<table>
  <tbody>
    <tr>
    {foreach from=$charList item=char}
      <td height="140px" align="center" width="140px" style="background-color:#333;">
		<table>
        
		<tbody>
			<td><input name="char" type="image" title="Register {$char.charName}" onclick="reg(&quot;{$char.charName}&quot;)" src="http://image.eveonline.com/Character/{$char.charID}_128.jpg" align="middle" />
		  <tr valign="top">
			<input type="hidden" name="{$char.charName}" value="{$char.charID}" /></td>
		  </tr>
		  <tr><td align="center" style="font-size:smaller;">{$char.charName}</td></tr>
		  <tr><td align="center" style="font-size:smaller;">{$char.corpName}</td></tr>
		</tbody>
		</form>
        </table>
	  </td>
    {/foreach}
    </tr>
  </tbody>
</table>

</form>
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}

