{* Smarty debug *}
{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
{capture assign="title"}Highscore ({$HIGHSCORE_STARTDATE} - {$HIGHSCORE_ENDDATE}){/capture}
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/menu.tpl"}
{* ------------------------------- *}

<table class="data" border="0" cellspacing="0" cellpadding="3">
  <thead>
  <tr class="headcol">
  {foreach from=$HIGHSCORE_MONATSARRAY item=monat}
   <td>  
        <form action="{$index}&action=highscore" method="post" style="float:left;margin:0 5px;">
    	  <input type="hidden" name="offset" value="{$monat.offset}" />
    	  <input type="hidden" name="offset2" value="{$monat.offset2}" />
   			<input type="hidden" name="modus" value="month" />
        <input name="submit" type="submit" value="{$monat.mtext}" />
        </form>
  </td>
  {/foreach}
  <tr>
   <td colspan="14">
        <form action="{$index}&action=highscore" method="post" style="float:left;margin:0 10px;">
    	  <input type="hidden" name="offset" value="{$offset-604800}" />
        <input type="hidden" name="modus" value="week" />
         <input name="submit" type="submit" value="&nbsp;&lt;&lt;&nbsp; zur&uuml;ck" />
        </form>
{if ($offset+604800<=$thisweek) }
        <form action="{$index}&action=highscore" method="post" style="float:left;margin:0 10px;">
    	  <input type="hidden" name="offset" value="{$thisweek}" />
        <input type="hidden" name="modus" value="week" />
         <input name="submit" type="submit" value="diese Woche" />
        </form>  
        <form action="{$index}&action=highscore" method="post" style="float:left;margin:0 10px;">
    	  <input type="hidden" name="offset" value="{$offset+604800}" />
    	  <input type="hidden" name="modus" value="week" />
         <input name="submit" type="submit" value="vor &nbsp;&gt;&gt;&nbsp;" />
        </form>
{/if}
   </td>
  </tr>
  <tr class="headcol">
   <td align="center" colspan=2>Position</td>
   <td align="left" colspan=8>Name</td>
   <td align="center" colspan=4>Dauer</td>
   </tr>
 </thead>
 <tbody>
{if $Highscore}
{foreach from=$Highscore item=score}
  <tr bgcolor="{cycle values="#444444,#333333"}">
    <td align="center" colspan=2>{$score.position}</td>
    <td align="left" colspan=8>{$score.name|capitalize} </td>
    <td align="center" colspan=4>{$score.zeit} </td>  
  </tr>
{/foreach}
{else}
  <tr bgcolor="#444444">
    <td colspan="14" align="center">There are no Records</td>
  </tr>
{/if}
 </tbody>
</table>

</br></br>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}