{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
<div id="title">&raquo; {$language.register}</div>
<div id="menu">
<ul class="items">
	<li id="selected">{$language.sel_char_alt}</li>
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>
<div style="width:1000px">
<h4>Please choose one account you want to add</h4>

<script type="text/javascript" src="inc/reg.js"></script>

<form action="{$url_dowork}" method="post" id="reg">
<input type="hidden" name="action" value="addAltCharApi" />
<table>
  <tbody>
    <tr>
    {foreach from=$charList item=char}
      <td height="140px" align="center" width="140px" style="background-color:#333;">
		<table>
        
		<tbody>
		  <tr valign="top">
			<td><input name="char" type="image" title="Register {$char.charName}" onclick="reg(&quot;{$char.charName}&quot;)" src="http://image.eveonline.com/Character/{$char.charID}_128.jpg" align="middle" />
			<input type="hidden" name="{$char.charName}" value="{$char.charID}" /></td>
		  </tr>
		  <tr><td align="center" style="font-size:smaller;">{$char.charName}</td></tr>
		  <tr><td align="center" style="font-size:smaller;">{$char.corpName}</td></tr>
		</tbody>
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

