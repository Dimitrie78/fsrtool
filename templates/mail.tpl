{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; {$language.forgot_password}</div>
<div id="menu">
<ul class="items">
	<li id="selected">E-mail {$language.information}</li>
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />
<div style="width:1000px">

<h4>{$language.change_password_use_form_below}</h4>
		<form action="{$url_dowork}" method="post">
		<table>
      <tr>
		    <td>{$language.username}:</td>
        <td><input type="text" name="username" size="20" /></td>
		  </tr>
		  <tr>
		    <td>Email:</td>
        <td><input type="text" name="mail" size="20" /></td>
		  </tr>
		  <tr>
		    <td colspan="2" align="center"><input type="submit" value="{$language.send}" /></td>
		  </tr>
		</table>
		<input type="hidden" name="action" value="getPassword" />
		<input type="hidden" name="SID" value="{$SID}" />
		</form>
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}

