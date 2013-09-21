{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

<br/>

<script type="text/javascript" src="modules/{$activeModule}/inc/settings.js"></script>


<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}
{else}<input type="button" value="Fetch API" name="update" id="update" />{/if}
<div id="result"></div>
<h4>{$language.pos_settings}</h4>
<ol><li>CorporationSheet</li><li>StarbaseList</li><li>StarbaseDetail</li><li>AssetList</li><li>Locations</li></ol>
<form action="{$url_dowork}" method="post">
    <table>
      <tr>
        <td>{$language.username}:</td>
        <td>{$ApiUser.UserName}</td>
      </tr>
	  <tr>
        <td>Corp:</td>
        <td>{$ApiUser.CorpName}</td>
      </tr>
      <tr>
        <td>EVE keyID:</td>
        <td><input type="text" name="user[id]" value="{$ApiUser.keyID}" size="15" /></td>
      </tr>
      <tr>
        <td>EVE vCODE:</td>
        <td><input type="text" name="user[api]" value="{$ApiUser.vCODEx}" size="75" /></td>
      </tr>      
    
      <tr>
		 <td colspan="2" align="center"><input type="submit" value="{$language.send}" /></td>
	  </tr>
	</table>
<input type="hidden" name="module" value="{$activeModule}" />
<input type="hidden" name="action" value="editAPI" />
</form>

<div style="width:600px;">
<form action="{$url_dowork}" method="post">
  <fieldset>
	<legend>Mail:</legend>
	<input type="text" name="mail[]" value="" size="35" />
	<input type="hidden" name="module" value="{$activeModule}" />
	<input type="hidden" name="action" value="mail" />
  </fieldset>
</form>
</div>
</div>
{if ($log)}<br>
<div align="center">
<table border="1">
  <tr>
    <td align="center">Datum</td>
	<td align="center">Comment</td>
  </tr>
{foreach from=$log item=thisLog}
  <tr>
    <td>{$thisLog.date|date_format:"%d.%m.%Y %H:%M"}</td>
	<td>{$thisLog.comment}</td>
  </tr>
{/foreach}
</table>
</div>
{/if}
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}