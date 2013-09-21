
<div id="menu">
<ul class="items">
	<li {if $action == '' or $action == 'accessMask'}id="selected"{/if}><a href="{$url_index}">{$language.overview}</a></li>
	<li {if $action == 'skills'}id="selected"{/if}><a href="{$url_index}?action=skills">Skills</a></li>
{if ($curUser->accessMask & 8) && ($curUser->accessMask & 131072) && ($curUser->accessMask & 262144)}
	<li {if $action == 'SkillSheet'}id="selected"{/if}><a href="{$url_index}?action=SkillSheet">Skill Sheet</a></li>
{else}<li id="disabled">Skill Sheet</li>{/if}
	<li {if $action == 'eveNotifications'}id="selected"{/if}><a href="{$url_index}?action=eveNotifications">eveNotifications</a></li>
	<li {if $action == 'chat'}id="selected"{/if}><a href="{$url_index}?action=chat">chat</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
