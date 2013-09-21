{* Smarty *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de" xml:lang="de">
  <head>
    <title>FSR-Tool</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="classes/style.css"/>
	
	<link type="text/css" href="classes/lib/uithemes/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
	<script type="text/javascript" src="classes/lib/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="classes/lib/jquery-ui-1.8.14.custom.min.js"></script>
	{*
	<link href="classes/lib/css/ui-darkness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<script src="classes/lib/js/jquery-1.8.3.js"></script>
	<script src="classes/lib/js/jquery-ui-1.9.2.custom.js"></script>
	*}
	<script type="text/javascript" src="classes/jqry_plugins/dg.js"></script>
	<script type="text/javascript" src="classes/jqry_plugins/jquery.confirm.js"></script>
	<script type="text/javascript" src="classes/jqry_plugins/jquery.qtip-1.0.0-rc3.js"></script>
	<script type="text/javascript" src="classes/jqry_plugins/qtip.style.js"></script>
	<script type="text/javascript" src="classes/jqry_plugins/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="classes/jqry_plugins/fsr-tool.js"></script>
	<link type="text/css" rel="stylesheet" media="all" href="chat/css/chat.css" />
	<link type="text/css" rel="stylesheet" media="all" href="chat/css/screen.css" />
	
	<!--[if lte IE 7]>
	<link type="text/css" rel="stylesheet" media="all" href="chat/css/screen_ie.css" />
	<![endif]-->
	{if $curUser->charID != ""}<script type="text/javascript" src="chat/js/chat.js"></script>{/if}
	{if isset($addheader)}{foreach from=$addheader item=this}{$this}{/foreach}{/if}

  </head>
	<body{if isset($bodytrust)}{$bodytrust}{/if}>
<div id="mask"></div>
<audio id="play">
  <source src="receive.wav" type="audio/wav">
  <source src="receive.mp3" type="audio/mp3">
</audio>
<div align="center" id="main_container">
{* ------------------------------- *}
{include file="bar.tpl"}
{* ------------------------------- *}
	<div style="background-color:#333;min-width:1000px;">
	{if !isset($msg_error)}
	<div style="padding:10px 0;width:1000px;">
		<div style="float:left;">{if $curUser->charID != ""}{$language.logged_in_as}: {$curUser->username} ( <a href="{$url_index}">{$language.profile}</a> | <a href="{$url_logout}">Logout</a> ){/if}</div>
		<div style="text-align:right;">
{if $curUser->charID != 0}			  
			  Chat: <select id="onlinechat" onChange="chatWith(this.value);this.selectedIndex = 0;">
				<option>online (-)</option>
			  </select>&nbsp;
{/if}
			<a href="{$url_index_language}=EN" title="Change Language &lt;br />to English"><img src="icons/22_32_37.png" width="16" height="16" alt="english" style="vertical-align:middle;"/></a> / <a href="{$url_index_language}=DE" title="Change Language&lt;br />to German"><img src="icons/22_32_35.png" width="16" height="16" alt="deutsch" style="vertical-align:middle;"/></a>
		</div>
	</div>
	{/if}
{if (($curUser->charID == "") AND !isset($msg_error))}
	<form action="{$url_dowork}" method="post">
	<input type="hidden" name="SID" value="{$SID}"/>
	<input type="hidden" name="action" value="login"/>
	<input type="hidden" name="request_url" value="{if isset($request_url)}{$request_url}{/if}"/>
	{assign var=myun value=$charName}
	  <table>
		<tr>
		  <td>{$language.username}:</td>
		  <td><input type="text" name="username" value="{$myun}" size="15"/></td>
		</tr>
		<tr>
		  <td>{$language.password}:</td>
		  <td><input type="password" name="password" value="" size="15"/></td>
		</tr>
		<tr>
		  <td>{$language.remember_me}?</td>
		  <td><input type="checkbox" name="check" value="1"/></td>
		</tr>
		<tr>
		  <td colspan="2" align="center"><input type="submit" value="login"/></td>
		</tr>
		<tr>
		  <td colspan="2" align="center"><a href="{if isset($url_email)}{$url_email}{/if}" title="Lost Password?">{$language.forgot_password}?</a></td>
		</tr>
	  </table>
	
	</form>

	<br />
	
{/if}

	{$Messages->getconfirms()}
	{$Messages->getwarnings()}

{*** /Header ***}
