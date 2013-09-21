{* Smarty *}
{************ index.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}
{* ------------------------------- *}

<div id="title">&raquo; {$language.my_profile}</div>
<div id="menu">
<ul class="items">
	<li><a href="{$url_index}">{$language.overview}</a></li>
	<li id="selected">{$language.change_password}</li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>

<h4>{$language.change_password_use_form_below}</h4>
		<form action="{$url_dowork}" method="post">
		<table>
		  <tr>
		    <td>
			  <input type="password" name="old" size="8" />
			</td>
			<td>
			  {$language.old_password}
			</td>
		  </tr>
		  <tr>
		    <td>
			<input type="password" name="new1" size="8" />
			</td>
			<td>
			{$language.new_password}
			</td>
		  </tr>
		  <tr>
		    <td>
			<input type="password" name="new2" size="8" />
			</td>
			<td>
			{$language.repeat}
			</td>
		  </tr>
		  <tr>
		    <td colspan="2" align="center">
			  <input type="submit" value="{$language.send}" />
			</td>
		  </tr>
		</table>
		<input type="hidden" name="action" value="changePassword" />
		<input type="hidden" name="SID" value="{$SID}" />
		</form>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}

