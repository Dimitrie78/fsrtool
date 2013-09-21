{include file="header.tpl"}
{include file="file:[Pos]menue.tpl"}

<div align="center">
{if $ApiStatus}{include file="file:[Pos]ApiStatus.tpl"}{/if}
<br/>
[center][b][size=200]FSR POS Standorte[/size][/b][/center]<br>

{assign var="name" value=''}
{assign var="region" value=''}

{foreach from=$poslist item=thisPos}
{if ($thisPos.region != $region)}<br>[size=150][b][u]{$thisPos.region}:	[/u][/b][/size]<br>
{if ($thisPos.manager==$name)}
{if ($thisPos.manager != "")}<br>[u][b]{$thisPos.manager}[/b][/u]<br>
{else}<br>[u][b]Online - Platzhalter[/b][/u]<br>{/if}
{/if}
{/if}



{if ($thisPos.manager!=$name)}
	{if ($thisPos.manager != "")}<br>[u][b]{$thisPos.manager}[/b][/u]<br>{else}<br>[u][b]Online - Platzhalter[/b][/u]<br>{/if}
{/if}
{$thisPos.moon}
{if isset($thisPos.mods)}[color=#FF8000]  &lt;&lt;{$thisPos.mods|escape}&gt;&gt;[/color]{/if}<br>

{assign var="name" value=$thisPos.manager}
{assign var="region" value=$thisPos.region}

{/foreach}
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}