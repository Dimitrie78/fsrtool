{* Smarty *}
{*** bar.tpl ***}

<div style="padding:5px 10px;background-color:#000;border-bottom:solid 1px #fff;text-align:right;min-width:1000px">
<div style="width:1000px;margin-left:auto;margin-right:auto;">
	<div id="loader" style="float:left;display:none;"><img src="icons/ajax-loader.gif" />&nbsp;</div>
	<div style="float:left;">FSR-Toolbar <a href="{$url_index}?action=About">About</a></div>
	<div>
	{if $curUser->charID != ""}
		{if $curUser->corpID == 147849586}<a href="https://www.free-space-ranger.org/forum" target="_blank" title="Hier geht es zum Forum der Free-Space-Ranger">FSR-Forum</a> // {/if}
		<a href="{$url_index}?module=ooe" title="Out of EvE">OOE</a> // 
		<a href="{$url_index}?module=eveorder" title="eveOrder">eveOrder</a> // 
		<a href="{$url_index}?module=Pos" title="Tower Online- und Fuel-Management Tool">POS</a>
		{if $curUser->SiloManager || $curUser->SiloAlt} // <a href="{$url_index}?module=Silo" title="Silo verwaltungstool">SILO</a>{/if}
		{if $curUser->DreadManager} // <a href="{$url_index}?module=Dread">Dread-tool</a>{/if}
		{if $curUser->Admin} // <a href="{$url_index}?module=jb" title="JumpBridgeFuel">JB</a>{/if}
		{if $curUser->Manager || $curUser->InduJobs} // <a href="{$url_index}?module=Productions" title="Industry">Productions</a>{/if}
		 // <a href="{$url_index}?module=Member" title="Membertool">Membertool</a>
	{else}&nbsp; {/if}
	</div>
</div>
</div>