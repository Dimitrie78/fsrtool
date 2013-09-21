{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

{if ($curUser->charID == "") }

<div id="title">&raquo; {$language.register}</div>
<div id="menu">
<ul class="items">
	<li id="selected">API {$language.information}</li>
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>
{include file="accessMask.tpl"}
<div style="width:1000px">

	{$language.register_eve_api} <a href="https://support.eveonline.com/api/Key/CreatePredefined/8388608" target="_blank">Get Eve-API</a>
	<br />
	<br />
	<form action="{$url_dowork_assign}" method="post">
	<input type="hidden" name="SID" value="{$SID}"/>
	<input type="hidden" name="action" value="assignAPI"/>
  <table>
	  <tr>
	    <td align="left">keyID:</td>
		<td align="left"><input type="text" name="userID" size="20" /></td>
	  </tr>
	  <tr>
	    <td align="left">vCODE:</td>
		<td align="left"><input type="text" name="apiKey" size="50" /></td>
	  </tr>
	  <tr>
	    <td colspan="2" align="center"><input type="submit" value="register" /></td>
	  </tr>
	</table>
	</form>
{*	{$language.howto_register} *}
</div>

{/if}

{if $curUser->charID != ""}

<div id="title">&raquo; {$language.my_profile}</div>
{include file="menu.tpl"}
<br/>
{include file="accessMask.tpl"}
<div style="width:1000px">

<form action="{$url_dowork}" method="post">
    <table cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
      <tr>
		<td rowspan="7"><img src="{getportrait charID=$curUser->charID}" style="border: 2px #000 solid;"/></td>
	  </tr>
	  <tr>
        <td width="110" align="left">{$language.username}:</td>
        <td align="left">{$curUser->username}</td>
      </tr>
	  <tr>
        <td width="110" align="left">Corp:</td>
        <td align="left">{$curUser->corpName}</td>
      </tr>
      <tr>
        <td width="110" align="left">Email:</td>
        <td align="left"><input type="text" name="user[mail]" value="{$curUser->email}" size="40" /></td>
      </tr>
      <tr>
        <td width="110" align="left">EVE keyID:</td>
        <td align="left"><input type="text" name="user[id]" value="{$curUser->keyID}" size="15" /></td>
      </tr>
      <tr>
        <td width="110" align="left">{if $curUser->active}<span style="color:#090;">EVE vCODE:</span>{else}<span style="color:#F00;">EVE vCODE:</span>{/if}</td>
        <td align="left"><input type="text" name="user[api]" value="{$curUser->apiX}" size="75" /></td>
      </tr>      
      
      <tr>
		<td width="110" align="left"><a href="{$url_index}?action=passwd">{$language.change_password}</a></td>
		<td align="left"><input type="submit" value="update" /></td>
	  </tr>
	</table>
	<input type="hidden" name="action" value="editUser" />
</form>
</div>
{if ($curUser->alts)}
<br/>
<div style="width:1000px">
    <table cellpadding="2" cellspacing="0" style="border: 2px #000 solid;">
    <thead>
      <tr bgcolor="#000000">
        <td colspan="3" align="center"><b>Alts</b></td>
      </tr>
    </thead>
    <tbody>
      {foreach name=altchars from=$curUser->alts item=thisAlt}
      <form action="{$url_dowork}" method="post">
      <tr>
		<td rowspan="6"><img src="{getportrait charID=$thisAlt.charID}" style="border: 2px #000 solid;"/></td>
	  </tr>
	  <tr>
        <td width="110" align="left">{$language.username}:</td>
        <td align="left">{$thisAlt.charName}</td>
      </tr>
	  <tr>
        <td width="110" align="left">Corp:</td>
        <td align="left">{$thisAlt.corpName}</td>
      </tr>
      <tr>
        <td width="110" align="left">EVE keyID:</td>
        <td align="left"><input type="text" name="alt[id]" value="{$thisAlt.userID}" size="15" /></td>
      </tr>
      <tr>
        <td width="110" align="left">{if $thisAlt.newAPI}<span style="color:#090;">EVE vCODE:</span>{else}<span style="color:#F00;">EVE vCODE:</span>{/if}</td>
        <td align="left"><input type="text" name="alt[api]" value="{$thisAlt.apiX}" size="75" /></td>
      </tr>      
      <tr>
		<td width="110" align="left">&nbsp;</td>
		<td align="right"><span style="float:left;"><input type="submit" name="alt[up]" value="update" /></span><input type="submit" name="alt[del]" value="delete" /></td>
	  </tr>
	  {if $smarty.foreach.altchars.total > 1 && !$smarty.foreach.altchars.last}
	  <tr><td colspan="3"><hr></td></tr>
	  {/if}
      <input type="hidden" name="alt[char]" value="{$thisAlt.charID}" />
      <input type="hidden" name="action" value="editUser" />
	  </form>
      {/foreach}
    </tbody>
	</table>
</div>
{/if} {* end if Alts *}

<br/>
<div style="width:1000px">
<form action="{$url_index}" method="post">
 <input type="hidden" name="action" value="addalt" />
 <table>
  <tr>
   <td><input type="submit" value="{$language.add_alt}" /></td>
  </tr>
 </table>
</form>
</div>

{/if}


{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}

