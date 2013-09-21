{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

<div id="title">&raquo; ADD&nbsp;Alt</div>
<div id="menu">
<ul class="items">
	<li id="selected">API {$language.information}</li>
</ul>
</div>
</div> {* end of div started in header.tpl *}

<br/>

<div style="width:1000px">
<a href="https://support.eveonline.com/api/Key/CreatePredefined/8388608" target="_blank">Get Eve-API</a>
<br />
<br />
<form action="{$url_dowork}" method="post">
 <input type="hidden" name="action" value="assignaltAPI"/>
<table>
 <tr>
  <td>keyID:</td>
  <td><input type="text" name="userID" size="20" /></td>
 </tr>
 <tr>
  <td>vCODE:</td>
  <td><input type="text" name="apiKey" size="65" /></td>
 </tr>
 <tr>
  <td colspan="2" align="center"><input type="submit" value="register" /></td>
 </tr>
</table>
</form>
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}