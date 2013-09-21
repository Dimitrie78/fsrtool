{include file="header.tpl"}

<div id="title">&raquo; About</div>
<div id="menu">
<ul class="items">
	<li id="selected">Fsr-Tool</li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>

<div style="width:1000px; text-align:left;">
<h2 style="text-align:center">Info über das FSR-Tool</h2>

<ol>
<li>Für die Regestrierug benötigen Sie einen EvE API Key der mindestens die Private CharacterInfo beinhaltet.</li>
<li>Falls noch kein account für Ihre corp vorhanden ist, erhält der erste registrierte Account die Manager rolle.</li>
</ol>


<a href="lightbox/images/examples/pos.png" rel="lightbox[roadtrip]" title="Tower list"><img src="lightbox/images/examples/pos.png"  height="128" width="128"  alt="Tower list" /></a>
<a href="lightbox/images/examples/pos1.png" rel="lightbox[roadtrip]" title="Fuel Bill"><img src="lightbox/images/examples/pos1.png"  height="128" width="128"  alt="Fuel Bill" /></a>
<a href="lightbox/images/examples/pos2.png" rel="lightbox[roadtrip]" title="Tower settings"><img src="lightbox/images/examples/pos2.png"  height="128" width="128"  alt="Tower settings" /></a>
<a href="lightbox/images/examples/silo1.png" rel="lightbox[roadtrip]" title="Silo anzeige"><img src="lightbox/images/examples/silo1.png"  height="128" width="128"  alt="Silo anzeige" /></a>
 
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}

