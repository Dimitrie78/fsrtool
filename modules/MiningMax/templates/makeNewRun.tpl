{* Smarty *}{*debug*}
{************ makeNewRun.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
{assign var=title value="Eine neue Mining Operation erstellen"}
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/menu.tpl"}
{* ------------------------------- *}

<form action="{$index}" method="POST">
<table class="mining" border="0" cellspacing="0" cellpadding="3">
 <thead>
  <tr>
   <th colspan="2">Eine neue Mining Operation erstellen</th>
  </tr>
 </thead>
 <tbody>
  <tr>
   <td>Ort:</td>
   <td>{if $locations}<select name="locations">{html_options options=$locations}</select><input type="hidden" name="location">{else}<input type="text" name="location" /> {/if}</td>
  </tr>
  <tr>
   <td>Verantwortlich:</td>
   <td>{if $seniorUsers}<select name="supervisor">{html_options options=$seniorUsers selected=$user}</select>{else}<input type="hidden" name="supervisor" />{$user} {/if}
  </tr>
  <tr>
   <td>Corporation beh&auml;lt:</td>
   <td>{$tax}</td>
  </tr>
  {if $isOfficial == "1"}
  <tr>
   <td>Corp Mining:</td>
   <td><input type="checkbox" name="isOfficial" CHECKED>Haken setzen f&uuml;r Corp Mining</td>
  </tr>
  {/if}
   <tr>
   <td>Projekt NYX OP:</td>
   <td><input type="checkbox" name="SPEZIALOP" >Haken setzen f&uuml;r Projekt OP Nyx</td>
  </tr>
  
  <tr>
  <td>
  Eve Serverzeit:
  </td>
  <td>
   {$TIMEMARK}
  </td>
  </tr>
  <tr>
   <td>Starttime:</td>
   <td><input type="text" name="ST_day"    size="2" maxlength="2" value="{$times.day}">.
	   <input type="text" name="ST_month"  size="2" maxlength="2" value="{$times.month}">.
	   <input type="text" name="ST_year"   size="4" maxlength="4" value="{$times.year}">
	   &nbsp;&nbsp;
	   <input type="text" name="ST_hour"   size="2" maxlength="2" value="{$times.hour}">:
	   <input type="text" name="ST_minute" size="2" maxlength="2" value="00">
       - or - <input type="checkbox" name="startnow" checked=true value="true"> sofort starten</td>
  </tr>
  <tr>
   <td colspan="2" align="center"><hr><input type="hidden" value="addrun" name="action">
                       <input type="submit" value="Create new Mining Operation" name="submit"></td>
  </tr>
 </tbody>
</table>
</form>
</br></br>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}