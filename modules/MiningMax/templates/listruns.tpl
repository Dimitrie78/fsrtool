{* Smarty *}
{* debug *}
{************ userList.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
{assign var="title" value="Aktuelle Mining Operationen"}
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/menu.tpl"}
{* ------------------------------- *}

{* <h2>&Uuml;bersicht der Mining Operationen</h2> *}

<table class="data" border="0" cellspacing="0" cellpadding="3">
 <thead>
  <tr class="head">
   <th colspan="10">Aktuelle Miningruns ({$date})</th>
  </tr>
  <tr class="headcol">
   <td align="center">ID</td>
   
   <td align="center">Supervisor</td>
   <td align="center">Startzeit</td>
   <td align="center">Beendet um</td>
   <td align="center">Ertrag</td>
   <td align="center">System</td>
   <td align="center">Corp Steuer</td>
   <td align="center">Corpmining</td>
   <td align="center">Auszahlung</td>
   <td align="center" width="30">&nbsp;</td>
   
   
  </tr>
 </thead>
 <tbody>
  {foreach from=$runs item=run}
  <tr bgcolor="{cycle values="#444444,#333333"}">
    <td align="center">
	<a href="{$index}&action=show&id={$run.id}">Details: #{$run.id}</a>
    </td>

    <td align="center">{$run.supervisor|capitalize} </td>
    <td align="center">{$run.starttime|date_format:"%d.%m.%Y %H:%M"} </td>
    <td align="center">{if $run.endtime}{$run.endtime|date_format:"%d.%m.%Y %H:%M"}
      {else}
    {* op beitreten Button *}
       {if $run.zeige_beitreten==1 }
        <form action="{$index}&action=joinop" method="post">
    	  <input type="hidden" name="runid" value="{$run.id}" />
          <input name="submit" type="submit" value="beitreten" />
        </form>
       {/if} 
    {* op beitreten Button *}       
       {if $run.zeige_verlassen==1 }
        <form action="{$index}&action=leaveop" method="post">
    	  <input type="hidden" name="runid" value="{$run.id}" />
          <input name="submit" type="submit" value="verlassen" />
        </form>
       {/if}        
       
      {/if} </td>

    <td align="right">{$run.ertrag|commify:2:'.':','}</td>
    <td align="center">{$run.location} </td>
    <td align="center">{$run.corpkeeps} %</td>
  {assign var='Color1' value='#FBFBFB'}   {* Even *}
  {assign var='Color2' value='#CCCCCC'}   {* Odd *} 
  {if $run.isOfficial == '1'}
    <td align="center"><font color="#00ff00">Yes</font></td>
  {else}
   {if $run.tmec == 99}
    <td align="center"><font color="#8888ff">Projekt</font></td>
   {else}
    <td align="center"><font color="#ff0000">No</font></td>
   {/if} 
  {/if}
      <td align="center">
      {if $run.isLocked == '0' and $run.isOfficial == '0' and $run.corpkeeps<100 and $run.endtime}
       {if $MySelf->isAccountant()}


        <form action="{$index}&action=payout" method="post">
    	  <input type="hidden" name="runid" value="{$run.id}" />
          <input name="submit" type="submit" value="Pay Out #{$run.id}" />
        </form>





       {else}
        steht aus
       {/if}
      {else}
       {if $run.isOfficial == '0' }
        &nbsp;.
       {else}
       &nbsp;
       {/if}
        
      {/if}
      </td>


{* candelete *}      
      <td align="center">
       {if $run.endtime}
        {if $MySelf->canDeleteRun()== "1"} 
        
        <form action="{$index}&action=delrun" method="post">
    	  <input type="hidden" name="runid" value="{$run.id}" />
        <input type="image" src="icons/delete.png" alt="l&ouml;schen">
        </form>        
        
        
        {/if}
       {/if}
      </td>
  </tr>
{*  
  {if !$run.endtime}
  <tr colspan=9>
  <td colspan=3></td><td><center>beitreten</center></td><td colspan=5>
  </tr>
  {/if}  
*}
  {/foreach}
  <tr>
  </tbody><tfoot>

{if ($offset>0) }
<td colspan=4>
        <form action="{$index}&module=MiningMax&action=runlist&offset={$offset-1}" method="post">
    	            <input name="submit" type="submit" value="Vorherige Seite" />
        </form>
        </td>
        <td colspan=4>&nbsp;</td>
{else}
<td colspan=8>&nbsp;</td>
        
{/if}

<td colspan=4>
        <form action="{$index}&module=MiningMax&action=runlist&offset={$offset+1}" method="post">
    	            <input name="submit" type="submit" value="N&auml;chste Seite" />
        </form>
</td>
  </tr>
  </tfoot>
</table>

        
</br></br>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}