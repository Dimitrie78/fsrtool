{* Smarty *}
{************ Member.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; Membertool</div>
{include file="file:[Member]bar.tpl"}

</div> {* end of div started in header.tpl *}
<br/>
<div align="center">
<table border="1" cellspacing="0">
  <tr>
    <td valign="top">
<table>
  <tr>
	<td colspan="2" align="center" bgcolor="#666666">Information</td> 
  </tr>
  <tr>
    <td width="60"><b>Main:</b></td>
	<td>{$Member.name}</td>
  </tr>
  <tr>
    <td><b>Division:</b></td>
    <td>{$status[$Member.division]}</td>
  </tr>

  <tr>
    <td><b>Alts:</b></td>
    <td>{$altsNamen}</td>
  </tr>
</table>
    </td>
	<td valign="top">
<table>
  <tr>
    <td colspan="2" align="center" bgcolor="#666666">Evaluation</td>
  </tr>
{foreach from=$Eval item=thisEval}
  <tr>
    <td>{$thisEval.date}</td>
	<td>{if ($thisEval.evaluation == 0)}<img src="modules/Member/images/nb.gif">
		{elseif ($thisEval.evaluation == 1)}<img src="modules/Member/images/neg2.gif">
		{elseif ($thisEval.evaluation == 2)}<img src="modules/Member/images/neg1.gif">
		{elseif ($thisEval.evaluation == 3)}<img src="modules/Member/images/neut.gif">
		{elseif ($thisEval.evaluation == 4)}<img src="modules/Member/images/pos1.gif">
		{elseif ($thisEval.evaluation == 5)}<img src="modules/Member/images/pos2.gif">
		{/if}
	</td>
  </tr>
{/foreach}
</table>	
	</td>
  <tr>
</table>
			
<table>
  <tr>
    <td style="background-color:#999999; color:black" align="center">{$language.member_1}</td>
  </tr>
</table>                                   
<br/>

<form action="{$url_dowork}" method="post">
<table>
  <tr>
    <td colspan="4" align="center" bgcolor="#666666">Details</td>
  </tr>                 
  <tr>
 	<td><b>Name: </b></td>
 	<td align="center"><b>Carrier: </b></td>
 	<td align="center"><b>Dread: </b></td>
 	<td align="center"><b>POS-Gunner: </b></td>
  </tr>
  <tr>
    <td>{$Member.name}</td>
	<td align="center">{html_options name=maincarrier options=$carrier selected=$Member.carrier}</td>
 	<td align="center">{html_options name=maindread options=$dread selected=$Member.dread}</td>
	<td align="center"><input type="checkbox" name="posgunner[{$Member.charID}]" value="1" {if ($Member.posgunner == 1)} checked="checked"{/if}></td>
  </tr><input type="hidden" name="main" value="{$Member.charID}">
{if isset($alts)}
{foreach from=$alts item=thisAlt}

  <tr>
    <td>{$thisAlt.name}</td>
	<td align="center">{html_options name="carrier[]" options=$carrier selected=$thisAlt.carrier}</td>
 	<td align="center">{html_options name="dread[]" options=$dread selected=$thisAlt.dread}</td>
	<td align="center"><input type="checkbox" name="posgunner[{$thisAlt.charID}]" value="1" {if ($thisAlt.posgunner == 1)} checked="checked"{/if}></td>
  </tr><input type="hidden" name="alt[]" value="{$thisAlt.charID}">
{/foreach}
{/if}
  <tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <td><b>{$language.timezone}: </b></td>
	<td align="center">{html_options name=tz options=$timeZ selected=$Member.tz}</td>
	<td colspan="2"> </td>
  </tr>
  <tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <td><b>{$language.absent}: </b></td>
	<td colspan="3"><input type="checkbox" name="afk" value="1" {if ($Member.afk == 1)} checked="checked"{/if}></td>
  </tr>
  <tr>
    <td><b>{$language.reason}: </b></td>
	<td colspan="3"><input name="afk_reason" type="text" size="60" value="{$Member.afkText}"></td>
  </tr>
  <tr>
 	<td colspan="4">
		<center>
			<input type="hidden" name="new_values" value="1">
			<input type="submit" value="submit" class="submit">
	  	</center>
	</td>
  </tr>
</table>
</form>

<table>
  <tr>
    <td style="background-color:#999999; color:black" align="center">{$language.member_2}</td>
  </tr>
</table>
<br />

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('#search').autocomplete({
		minLength: 3,
		source: "dowork.php?module=Member&action=search"
	});
});
</script>{/literal}

{$language.char_search}
<form action="{$url_dowork_search}" method="post">
<input type="text" id="search" name="search" size="45"/>
<input type="submit" value="{$language.send}" />
</form>
{if ($Member_s)}
<table>
  <tr>
	<td colspan="2" align="center" bgcolor="#666666"> Information form Search</td> 
  </tr>
  <tr>
    <td><b>Main:</b></td>
	<td>{$Member_s.name}</td>
  </tr>
  <tr>
    <td><b>Division:</b></td>
    <td>{$status[$Member_s.division]}</td>
  </tr>

  <tr>
    <td><b>Alts:</b></td>
    <td>{$alts_s}</td>
  </tr>
</table>
{/if}
{if ($HighCommand || $Leader || $curUser->Admin)}
<p>{include file="file:[Member]afk.tpl"}</p>
{/if}
</div>
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}