{include file="header.tpl"}    
{include file="file:[eveorder]menu.tpl"}   

{* <div style="background:#F63; color:#000; font-size:16px; font-weight:bold; width:500px"> This Tool is Beta Version...not Final :-) </div><br /> *}

<table border="0" cellpadding="0" cellspacing="0" style="min-width:1000px">
  <tr>
    <td valign="top" align="left" width="400">
    <div id="fittings"> 
	
	<div id="fittop">
	  <ul class="top">
	    <li id="User" class="selected">User</li>
	    <li id="Corp">Corp</li>
	    <li id="Ally">Ally</li>
        <li id="add" class="right">Add</li>
	  </ul>
	</div>
	<div id="fittings1">
    <ul class="f_class" style="text-align:left;">
    {foreach from=$fitts item=group key=class}  
     <li id="{$class|replace:' ':''}"><a href="#">{$class}</a>
      <ul class="f_ship" style="">
      {foreach from=$group item=this key=ship}
       <li id="{$ship|replace:' ':''}"><a href="#">{$ship}</a>
        <ul class="f_fit">
        {foreach from=$this item=fitt}          
         <li class="shipFitt" id="{$fitt.fittID}">{$fitt.name}</li>
        {/foreach}
        </ul>
       </li>
      {/foreach}
      </ul>
     </li>
    {/foreach}
    </ul>
	</div>
       
	</div>
    <br />
	{include file="file:[eveorder]fitting.tpl"}
    </td>
	<td valign="top" align="center" width="600"><div id="layout"></div></td>
    
  </tr>
</table>

<div id="addFit">EFT Fitting<br /><textarea name="fitt" id="fitt" cols="50" rows="30"></textarea>
	<p><input type="button" class="saveButton" value="Add" />
	<input type="button" class="closeButton" value="Close" /></p>
</div>

{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}