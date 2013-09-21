  <tr>
    <td style="width:30%">Name</td>
	<td style="width:60%; text-align:center"><b>Evaluation</b> new -> old</td>
	<td style="width:10%; text-align:center">&nbsp;</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$list key=key item=thisList}
	{if (($eveTime-$thisList.joined) > 60*60*24*23) and (($eveTime-$thisList.joined) < 60*60*24*32)}<tr bgcolor="#52c8f2">
	{elseif ($thisList.inactive == "1")}<tr bgcolor="#ffa9a9">
	{elseif (($eveTime-$thisList.lastSeen) > 60*60*24*15)}<tr bgcolor="#fdff5e">
	{elseif (($eveTime-$thisList.joined) < 60*60*24*30)}<tr bgcolor="#b9f0b9">
	{else}<tr bgcolor="#ffffff">
	{/if}
  <td>{renderNameNew char=$thisList}</td>
  <td>{if !$thisList.eval}&nbsp;
  {else}
  {foreach from=$thisList.eval item=thisEval}
	{if     ($thisEval.evaluation == "0")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/nb.gif"></a>
	{elseif ($thisEval.evaluation == "1")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/neg2.gif"></a>
	{elseif ($thisEval.evaluation == "2")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/neg1.gif"></a>
	{elseif ($thisEval.evaluation == "3")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/neut.gif"></a>
	{elseif ($thisEval.evaluation == "4")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/pos1.gif"></a>
	{elseif ($thisEval.evaluation == "5")}<a href="javascript:showEditEvalWin('{$thisList.charID}', '{$thisEval.evaluation}', '{$thisEval.comment}', '{$thisEval.date}', '{$index}', '{$eva}', '{$action}')" {if $thisEval.comment != ""}title="{$thisEval.comment}"{/if}><img src="{$urlMember}/images/pos2.gif"></a>
	{else}&nbsp;
	{/if}
  {/foreach}
  {/if}
  </td>
  <td align="center"><a href="javascript:showAddEvalWin('{$thisList.charID}', '{$index}', '{$eva}', '{$action}')"><img src="{$urlMember}/images/addeval.gif"></a></td>
 </tr>
{/foreach}