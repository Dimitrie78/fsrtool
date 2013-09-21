{include file="header.tpl"}
<div id="title">&raquo; Chat</div>
{include file="menu.tpl"}
<div>
{foreach from=$chatuser item=user}
	<a href="javascript:void(0)" onclick="javascript:chatWith('{$user.bname}')">{$user.aname}</a><br />
{/foreach}
</div>
{include file="footer.tpl"}    