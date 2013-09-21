{* Smarty *}
{* debug *}
{************ showRun.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
{literal}
<script type="text/javascript" src="classes/jqry_plugins/jquery.ba-dotimeout.min.js"></script>
<script type="text/javascript" src="classes/jqry_plugins/fwtimer.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('span.timer').each(function(){
		$(this).fwtimer();
	});
});
</script>
{/literal}
<div id="title">&raquo; MiningMax - Mining Operation Information</div>
<div id="menu">
<ul class="items">
	<li id="selected"><a href="{$index}">Aktuelle Mining Operationen</a></li>

  {***************** HAULERN START ********************}
  {* wenn das eine offene OP ist und derjenige Haulern darf und die OP auch läuft: *}
  {if $isrunning == 1}
     {if $general.duration == "ACTIVE"}
         {if ($MySelf->canAddHaul()) }
           { if ($userisinop >0 ) }
       <li>
        <form id="opaction_haul"  action="{$index}&action=addhaulpage" method="post">
    	  <input type="hidden" name="runid" value="{$general.miningID}" /></input>
        <a href="javascript:document.getElementById('opaction_haul').submit()">haulern</a>
        </form>
        </li>
        {/if} 
       {/if} 
     {/if} 
  {/if}
  {***************** HAULERN ENDE **********************}

  {***************** BETRETEN/VERLASSEN START ********************}
    {if $general.duration == "ACTIVE"}
  { if ($MySelf->canJoinRun()) }
  { if ($userisinop == "none") }
     {* <pre>CAN JOIN</pre> *}
     <li>
        <form id="opaction_join" action="{$index}&action=joinop" method="post">
    	  <input type="hidden" name="runid" value="{$general.miningID}" />
        <a href="javascript:document.getElementById('opaction_join').submit()">beitreten</a>
        </form>
     </li>
     {else}
     {* <pre>CAN LEAVE</pre> *}
             <li>
             <form id="opaction_leave" action="{$index}&action=leaveop" method="post">
    	  <input type="hidden" name="runid" value="{$general.miningID}" />
        <a href="javascript:document.getElementById('opaction_leave').submit()">verlassen</a>
        </form>
     </li>
     
  {/if}
  {/if}
  {/if}
  {***************** BETRETEN/VERLASSEN ENDE ********************}

 {*Projekt*}
<li><a href="{$index}&action=project">Projekt</a>
 {*Projekt*}
  {***************** eveorder Usermanager ********************}

	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}


   {***************** ENDRUN / CloseOP START ********************}
  {* wenn das eine offene OP ist. SpezialOPs darf nur ein ADMIN zu machen *}
     {if $general.duration == "ACTIVE"}
         {if ($MySelf->canAddHaul()) }
           { if (userInRun($MySelf->getID())) }
        <li class="right">
        <form id="opaction_endrun" action="{$index}&action=endrun" method="post">
    	  <input type="hidden" name="runid" value="{$general.miningID}" />
        <a href="javascript:document.getElementById('opaction_endrun').submit()">OP beenden</a>
        </form>
        </li>
        {/if} 
       {/if} 
     {/if} 
  
  {***************** ENDRUN / CloseOP ENDE ********************}





</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />

<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="2" style="padding-bottom:2px;"><span class="head">General Information #{$general.miningID} - {$general.location} - {$general.Supervisor}
{if $general.SPEZIALOP == 99} 
 - Spezial OP - Projekt NYX
{/if}
</td>
  </tr>
 </thead>
 <tbody>
  <tr><td>
  
  <table border="0" cellspacing="0" cellpadding="3">
   <tr><td>Dauer:</td><td>{$general.Starttime}</td>
   
   {if $general.duration == "ACTIVE"}
   <td> -</td><td colspan="3">
   {if $isrunning == 1}
   <span style="color:#00ff00;">Aktiv seit <span class="timer">{$general.secRunTime}</span></span></td>
   {else}
   <td><span style="color:#ff0000;">Warte auf Start</span></td>
   {/if}
   </tr>
   {else}
    <td> - {$general.endTime}</td>
   <td>({$general.duration})</td></tr>
   {/if}
   <tr><td>Corp Mining:</td>
    <td>{if $general.official == "0"}<span style="color:red;">Nein</span>{else}<span style="color:#00ff00;">Ja</span>{/if}</td>
   
   <td>Corp Steuer:</td><td>{$general.CorpTaxes} %
   {if $general.official == "0"}
   <td>Ausgezahlt:</td>
    <td>{if $general.payed == "0"}<span style="color:red;">Nein</span>{else}<span style="color:#00ff00;">Ja</span>{/if}</td>
   </tr>
   {/if}
   
  </table>
  
  </td></tr>
 </tbody>
</table><br/>
{* ------------------------------- Auszahlungsinfos Start ------------------------ *}
{if $general.duration <> "ACTIVE"}

<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="3">&raquo; Auszahlungsinformation</td>
  </tr>
  <tr class="headcol">
   <td>Pilot</td>
   <td>Betrag</td>
   <td>Anteil</td>
  </tr>
 </thead>
 
{if $PayinfoTotal>0 } 
 <tbody>
 {foreach from=$Payinfo item=thisUser}
 {if $thisUser.pilotname <>""}
 {if $thisUser.charity==0}
	<tr bgcolor="{cycle values="#444444,#333333"}">
   <td width=350>{$thisUser.pilotname}</td>
 	 <td align=right>{$thisUser.betrag|commify:2:'.':','}</td>
 	 <td align=right>{$thisUser.prozent|commify:2:',':'.'}%</td>
  </tr>

  {/if}
  {/if}
	{/foreach} 
 </tbody><tfoot> 
  <tr>
   <td><b>Total:</b></td>
   <td align="right"><b>{$PayinfoTotal|commify:2:'.':','}</b></td>
   <td align="right"><b>{$PayinfoTotalPercent|commify:2:',':'.'}%</b></td>
  </tr>
 </tfoot>
{else}
 <tbody>
 <tr bgcolor="#444444">
 <td colspan="3" width=350>
  Nichts auszubezahlen.
  </td>
  </tr>
</tbody>
  
{/if} 
</table><br/>

{/if}
{* ------------------------------- Auszahlungsinfos Ende ------------------------ *}

{* ------------------------------- Aktive Piloten Start ------------------------ *} 	
{if $general.duration == "ACTIVE"}
<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="{$Join.colspan}">&raquo; 
{if $isrunning == 1}   
   Aktive Piloten 
{else}
   Wartende Piloten   
{/if}   
   ({$Join.activeUser})</td>
  </tr>
  <tr class="headcol">
   <td>Pilot</td>
	 <td>beigetreten</td>
	 <td>Aktive Zeit</td>
	 <td>Status</td>
	 <td>Schiffstype</td>
	 <td>Charity</td>
	 {if $icankick==1}
	 <td>Entfernen</td>
{*
	 <td>Kick</td>
	 <td>Ban</td>
*}
   {/if}	 

  </tr>
 </thead>
 <tbody>
	{foreach from=$Join.active item=thisUser}
	<tr bgcolor="{cycle values="#444444,#333333"}">
   <td>{$thisUser.user}</td>
	 <td>{$thisUser.joined}</td>
	 <td>{if $thisUser.state == "1"}<span class="timer">{$thisUser.timer}</span>{else}{$thisUser.time}{/if}</td>
	 <td>{if $thisUser.state == "1"}<span style="color:#00ff00;">ACTIVE</span>{else}<span style="color:red;">wartend</span>{/if}</td>
	 <td>{$thisUser.shiptype}</td>
	 <td>{if $thisUser.charity == "1"}
	 	 {if $general.official == "0"}
	 <span style="color:#00ff00;">Yes</span>
	 {else}
	 Corp.
	 {/if}
	 {else}
	 {if $general.official == "0"}
	 <span style="color:red;">No</span>
	 {else}
	 Corp.
	 {/if}
	 {/if}</td>

	 <td>	 
	 {* REMOVE Button *}
	 {if ($icankick==1)}
	 
	 
	 {if ($thisUser.userID<>$MySelf->getID())  }

	         <form action="{$index}&module=MiningMax&action=kickban" method="post">
                  <input type="hidden" name="runid" value="{$general.miningID}" />
                  <input type="hidden" name="joinid" value="{$thisUser.ID}" />
                  <input type="hidden" name="state" value="1" /> 
    	            <input name="submit" type="submit" value="Entfernen" />
    	         <a href="#" title="Die ISK werden dem User<br/>trozdem gut geschrieben." >?</a>   
           </form>
{*	 
	 
	 <td>Kick</td>
	 <td>Ban</td>
*}	 
	 {/if}
	 {/if}
  </td>	 
  </tr>
	{/foreach}
 </tbody>
</table><br/>
{/if}  	
{* ------------------------------- Aktive Piloten Ende  ------------------------ *} 	  	
{* ------------------------------- Teilnehmer Log Start ------------------------ *} 	
{if $isrunning == 1}
<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="8">&raquo; Teilnehmer log</td>
  </tr>
  <tr class="headcol">
   <td>Pilot</td>
	 <td>beigetreten</td>
	 <td>verlassen</td>
	 <td>Aktive Zeit</td>
	 <td>Status</td>
	 <td>Charity</td>
	 <td>Total</td>
	 <td>Hinweis</td>
  </tr>
 </thead>
 <tbody>
	{foreach from=$Join.attendance item=thisUser}
	<tr bgcolor="{cycle values="#444444,#333333"}">
   <td>{$thisUser.user}</td>
	 <td>{$thisUser.joined}</td>
	 <td>{$thisUser.parted.time}</td>
	 <td>{$thisUser.parted.total}</td>
	 <td>{if $thisUser.parted.state == "1"}<span style="color:#00ff00;">ACTIVE</span>{else}INACTIVE{/if}</td>
	 <td>{if $thisUser.charity == "1"}
	 	 	 	 {if $general.official == "0"}
	 <span style="color:#00ff00;">Yes</span>
	 {else}
	 Corp.
	 {/if}
	 {else}
	 	 {if $general.official == "0"}
	 <span style="color:red;">No</span>
	 {else}
	 Corp.
	 {/if}
	 
	 {/if}</td>
	 <td>{$thisUser.sumtime}</td>
	 <td>{$thisUser.reason}</td>
    </tr>
	{/foreach}
 </tbody>
</table><br/>
{/if}
{* ------------------------------- Teilnehmer Log Ende ------------------------ *} 	
{* ------------------------------- Kommentar  Start ------------------------ *} 	

{if $general.duration == "ACTIVE"}

 <table class="data" cellspacing="0" cellpadding="3">
 <thead>
 <tr>
  <td colspan="8">&raquo; Kommentar</td>
 </tr>
 <tr class="headcol">
  <td>Kommentar</td>
  {* COMMENT *}
 <tr bgcolor="{cycle values="#444444,#333333"}">
  <td>
  <form action="{$index}&action=comment" method="post">
  <textarea cols="140" rows="5" name="comment">{$general.comment}</textarea>
  </td></tr>
  <tr><td>
  <input type="hidden" name="runid" value="{$general.miningID}" />
  <input type="hidden" name="joinid" value="{$thisUser.ID}" />
  <center><input name="submit" type="submit" value="Kommentar speichern" /></center>
  </form>
  </td></tr>
</tbody>
</table><br/>

{else}


<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="8">&raquo; Kommentar</td>
  </tr>
  <tr class="headcol">
   <td>Kommentar</td>
 <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>
 
   <textarea cols="140" rows="5" name="comment">{$general.comment}</textarea>
  
   </td>
   </tr>
</tbody>
</table><br/>


{/if}   

{* ------------------------------- Kommentar  Ende ------------------------ *} 	

  
  {***************** SPEZIALOP (99) START ********************}
  {* 
  {if $general.SPEZIALOP == 99}
<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="5">&raquo; Dies ist eine SpezialOP mit einer Zielvorgabe:</td>
  </tr>
  <tr class="headcol">
   <td colspan="1">Metall</td>
	 <td align="center">Vorhanden</td>
	 <td align="center">Ziel</td>
	 <td align="center">Rest</td>
	 <td align="center">am meisten in:</td>
  </tr>
 </thead>
 <tbody>

{if $S_Tritanium > 0}
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Tritanium</td>
	 <td align="right">{$S_Tritanium|commify:0:',':'.'} ( { $SP_Tritanium|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Tritanium|commify:0:',':'.'}</td>
   {if ($general.Tritanium-$S_Tritanium>1)} 	 
	 <td align="right">{$general.Tritanium-$S_Tritanium|commify:0:',':'.'}</td>
	 {else}
   <td>&nbsp</td>
	 {/if}
	 <td align="right">Veldspar,Scordite</td>
  </tr>
{/if}  

{if $S_Pyerite > 0}
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Pyerite</td>
	 <td align="right">{$S_Pyerite|commify:0:',':'.'} ( { $SP_Pyerite|commify:3:',':'.'} %) </td>
 	 <td align="right">{$general.Pyerite|commify:0:',':'.'}</td>
   {if ($general.Pyerite-$S_Pyerite>1)} 	 
	 <td align="right">{$general.Pyerite-$S_Pyerite|commify:0:',':'.'}</td>
	 {else}
   <td>&nbsp</td>
	 {/if}	
	 <td align="right">Plagioclase,Scordite</td>
  </tr>
{/if}

{if $S_Mexallon > 0}
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Mexallon</td>
	 <td align="right">{$S_Mexallon|commify:0:',':'.'} ( { $SP_Mexallon|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Mexallon|commify:0:',':'.'}</td>
   {if ($general.Mexallon-$S_Mexallon>1)} 	 
	 	 <td align="right">{$general.Mexallon-$S_Mexallon|commify:0:',':'.'}</td>
	 {else}
  	 <td>&nbsp</td>
	 {/if}	 	 
	 	 <td align="right">Kernite,Jaspet</td>
  </tr>
{/if}

{if $S_Isogen > 0}
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Isogen</td>
	 <td align="right">{$S_Isogen|commify:0:',':'.'} ( { $SP_Isogen|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Isogen|commify:0:',':'.'}</td>	 
   {if ($general.Isogen-$S_Isogen>1)} 	 
	 <td align="right">{$general.Isogen-$S_Isogen|commify:0:',':'.'}</td>
	 {else}
  	 <td>&nbsp</td>
	 {/if}		 
	 <td align="right">Hedbergite,Kernite</td>
  </tr>
{/if}

{if $S_Megacyte > 0}
    <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Megacyte</td>
	 <td align="right">{$S_Megacyte|commify:0:',':'.'} ( { $SP_Megacyte|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Megacyte|commify:0:',':'.'}</td>	 
   {if ($general.Megacyte-$S_Megacyte>1)} 	 
	 <td align="right">{$general.Megacyte-$S_Megacyte|commify:0:',':'.'}</td> 	 	 
	 {else}
  	 <td>&nbsp</td>
	 {/if}	 
	 <td align="right">Arkonor,Bistot</td>
  </tr>
{/if}

{if $S_Zydrine > 0}  
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Zydrine</td>
	 <td align="right">{$S_Zydrine|commify:0:',':'.'} ( { $SP_Zydrine|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Zydrine|commify:0:',':'.'}</td>	 
   {if ($general.Zydrine-$S_Zydrine>1)} 	 
	 <td align="right">{$general.Zydrine-$S_Zydrine|commify:0:',':'.'}</td> 	 	 	 
	 {else}
  	 <td>&nbsp</td>
	 {/if}
	 <td align="right">Crokite,Bistot</td>
  </tr>
{/if}

{if $S_Nocxium > 0}
    <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Nocxium</td>
	 <td align="right">{$S_Nocxium|commify:0:',':'.'} ( { $SP_Nocxium|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Nocxium|commify:0:',':'.'}</td>	 
   {if ($general.Nocxium-$S_Nocxium>1)} 
	 <td align="right">{$general.Nocxium-$S_Nocxium|commify:0:',':'.'}</td> 
	 {else}
  	 <td>&nbsp</td>
	 {/if}
	 <td align="right">Dark Ochre,Hemorphite</td>
  </tr>
{/if}

{if $S_Morphite > 0}
  <tr bgcolor="{cycle values="#444444,#333333"}">
   <td>Morphite</td>
	 <td align="right">{$S_Morphite|commify:0:',':'.'} ( { $SP_Morphite|commify:3:',':'.'} %)</td>
	 <td align="right">{$general.Morphite|commify:0:',':'.'}</td>	
	 {if ($general.Morphite-$S_Morphite>1)} 
  	 <td align="right">{$general.Morphite-$S_Morphite|commify:0:',':'.'}</td>
	 {else}
  	 <td>&nbsp</td>
	 {/if}
	 <td align="right">Mercoxit</td>
  </tr>  
{/if}


 </tbody><tfoot>
	<tr><td colspan=5>&nbsp;(*) Alle Werte bei 100% Refining errechnet!</tr>
 </tfoot>
</table><br/>
  {/if}
   *}
  {***************** SPEZIAL OP ENDE **********************}
  
{* --------------------- Ertraege erz Start -------------------- *}
{if $general.duration <> "ACTIVE"}
<table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="5">&raquo; Ertrag</td>
  </tr>
  <tr class="headcol">
   <td colspan="2">Erz</td>
	 <td align="center">Menge</td>
	 <td align="center">Wert</td>
   <td align="center">Gesamtwert</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$hauler item=thisOre}
  <tr bgcolor="{cycle values="#444444,#333333"}">
	 <td><img width="32" height="32" src="modules/MiningMax/images/ore/{$thisOre.picture}"</img></td>
   <td>{$thisOre.name}</td>
	 <td align="right">{$thisOre.menge}</td>
	 <td align="right">{$thisOre.wert|commify:2:',':'.'}</td>
	 <td align="right">{$thisOre.gesamtwert|commify:2:',':'.'}</td>
  </tr>
{/foreach}
 </tbody><tfoot>
	<tr><td colspan=4>Summe Brutto</td><td align="right">{$oretotalworth|commify:2:',':'.'}   </td></tr>
	<tr><td colspan=4>Steuer      </td><td align="right">{$oretotaltax|commify:2:',':'.'}	    </td></tr>
  <tr><td colspan=4><b>Netto</b></td><td align="right"><b>{$oretotalnetworth|commify:2:',':'.'}</tr>
 </tfoot>
</table><br/>
{/if}
 {* --------------------- Ertraege erz Ende -------------------- *}
 {* --------------------- Transport Start -------------------- *}
{if $transportcount>0} 
 <table class="data" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <td colspan="3">&raquo; Transporte</td>
  </tr>
  <tr class="headcol">
   <td style="width: 150px;">Name</td>
	 <td style="width: 100px;">Zeit</td>
	 <td style="width: 150px;">Fracht</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$transport item=thisTransport}
  <tr valign="top"  bgcolor="{cycle values="#444444,#333333"}">
	 <td>{$thisTransport.name|capitalize}</td>
	 <td>{$thisTransport.date}</td>
	 <td>{foreach from=$thisTransport.erz item=haul} 
	      {$haul.menge} - {$haul.name}<br /> 
		  {/foreach}
   </td>
  </tr>
{/foreach}
 </tbody>
</table>
{/if}
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}