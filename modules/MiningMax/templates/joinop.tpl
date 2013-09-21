{* Smarty *}{*debug*}
{************ userList.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; MiningMax -- Mining Op #{$runid} beitreten</div>
<div id="menu">
<ul class="items">
	<li><a href="{$index}">Aktuelle Mining Operationen</a></li>
		<li><a href="{$index}&action=show&runid={$runid}">zur&uuml;ck</a></li>
	{if $curUser->manager || $curUser->admin}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />
<br />
{*  STEP 2 - Schiffsauswahl + Anzahl + Charity *}
{if $SMARTY_JOINOP_STEP == 2}
 <table class="mining" border="0" cellspacing="0" cellpadding="3">
  <thead>
   <tr>
     <th colspan="2" align="center">Bitte ausw&auml;hlen</th>
   </tr>   
  </thead>
 <form action="{$index}&action=joinop" method="post">
 
  <tr>
   <td>Schiffsauswahl</td>
   <td><select name='shiptype'>{html_options options=$shiptypes}
  {*<option value="9">Miner</option> <option value="10">Hauler</option> <option value="2">Tank</option> <option value="4">Bonus</option> <option value="7">PvP</option><option value="21">Pressen</option>*}
   </select></td>
  </tr>
  <tr>
  {if $isOfficial == 0}
     <td>Mit wievielen Accounts nimmst Du teil?</td>
   <td><select name='num-of-accounts'><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option></select></td>
  {else}
  <td>&nbsp;</td>
   <td>    <input type="hidden" name="num-of-accounts" value="1"></td>
  {/if} 
  </tr>
  
  <tr>
  {if ($SPEZIALOP == 99) }
    <td>&nbsp;</td>
    <td>    
    {* <input type="hidden" name="wants-charity" value="Nein">    *}
    </td>
  {else}
  {* 14.03.2011 == 0  Charity ausgebaut -1 *}
   {if $isOfficial == -1 }
    <td>F&uuml;r die Corp:</td>
    <td><select name='wants-charity'><option>Nein</option><option>Ja</option></select></td>
    {else}
    <td>&nbsp;</td>
    <td>
        {* <input type="hidden" name="wants-charity" value="Ja">    *}
    </td>
   {/if} 
  {/if} 
  
  </tr>
  
  
 
  <tr>
   <td colspan="2" align="center"><hr><input type="submit" name="submit" value="weiter"></td>
  </tr>
 <input type="hidden" name="runid" value="{$runid}">
 <input type="hidden" name="confirmed-ship" value="true">
 <input type="hidden" name="confirmed" value="true">
 <input type="hidden" name="step" value="3">
 <input type="hidden" name="multiple" value="true">
 </form>
 </tbody>
 </table>
 {* STEP 2 ende *}
{/if}


</br>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}