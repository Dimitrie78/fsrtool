<script type="text/javascript" src="classes/jqry_plugins/jquery.ba-dotimeout.min.js"></script>
<script type="text/javascript" src="modules/ooe/inc/skillSheet.js"></script>

<br />

  <table align="center" width="70%" summary="Main" style="font-size: 8pt; background: black">
  <tbody>
    <tr>
      <td><br />
      <div style="float:right;position:relative;top:12px;margin-left:-120px">
      
	  {if $curUser->alts}
	    &nbsp;&nbsp;<a href="{$url_index}?action=SkillSheet" title="{$curUser->username}"><img src="http://image.eveonline.com/Character/{$curUser->charID}_32.jpg" width="32" height="32" class="mbAvatar" style="border:1px solid gray" alt="{$curUser->username}" /></a>
      {foreach item=char from=$curUser->alts}
        &nbsp;&nbsp;<a href="{$url_index}?action=SkillSheet&amp;cid={$char.charID}" title="{$char.charName}"><img src="http://image.eveonline.com/Character/{$char.charID}_32.jpg" width="32" height="32" class="mbAvatar" style="border:1px solid gray" alt="{$char.charName}" /></a>
	  {/foreach}
	  {/if}
	  
      </div>
      <div style="margin-top: 20px; margin-bottom: 24px;">
        <div style="margin-top: 20px;">
          <div style="border-top: 1px solid rgb(67, 67, 67); border-bottom: 1px solid rgb(67, 67, 67); background: rgb(44, 44, 56) url(icons/skillsheet/charinfo.jpg) no-repeat scroll 74px 5px; margin-bottom: 10px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; height: 22px;"></div>
          <img alt="Character Info" src="icons/skillsheet/charinfo.png" style="border: 0px none ; width: 64px; height: 64px; top: -52px;" class="newsTitleImage" />
          <div style="margin-left: 82px;"></div>
          <br />
          <br />
        <table style="margin-left: 80px;" border="0" cellpadding="2" cellspacing="0" summary="Character Info">
        <tbody>
          <tr>
            <td valign="top">
              <a onmouseover="ShowContent('skillsidebox'); return true;" onmouseout="HideContent('skillsidebox'); return true;" style="color: gold; font-weight: bold; text-decoration: none;" href="http://myeve.eve-online.com/ingameboard.asp?a=bigshot&amp;cid={$characterID}"><img style="border: 1px solid gray;" alt="{$name}" src="http://image.eveonline.com/Character/{$characterID}_256.jpg" /></a>
            </td>
            <td valign="top"><table class="dataTable" border="0" cellpadding="2" cellspacing="0" width="460" summary="Character Info">
              <tbody>
                <tr>
                  <td colspan="2" class="dataTableHeader">Info</td>
                  <td colspan="2" class="dataTableHeader">Attributes</td>
                </tr>
                <tr>
                  <td class="dataTableCell">Charactername</td>
                  <td class="dataTableCell">{$name}</td>
                  <td class="dataTableCell">Intelligence</td>
                  <td class="dataTableCell" align="center">{$attributes.intelligence}</td>
                </tr>
                <tr>
                  <td class="dataTableCell">Corporation</td>
                  <td class="dataTableCell">{$corporationName}</td>
                  <td class="dataTableCell">Perception</td>
                  <td class="dataTableCell" align="center">{$attributes.perception}</td>
                </tr>
                <tr>
                  <td class="dataTableCell">Total Cash</td>
                  <td class="dataTableCell">{$balance}</td>
                  <td class="dataTableCell">Charisma</td>
                  <td class="dataTableCell" align="center">{$attributes.charisma}</td>
                </tr>
                <tr>
                  <td class="dataTableCell">Race / Blood line</td>
                  <td class="dataTableCell">{$race} / {$bloodLine}</td>
                  <td class="dataTableCell">Memory</td>
                  <td class="dataTableCell" align="center">{$attributes.memory}</td>
                </tr>
                <tr>
                  <td class="dataTableCell">Total skill points</td>
                  <td class="dataTableCell">{$skillpointstotal}</td>
                  <td class="dataTableCell">Willpower</td>
                  <td class="dataTableCell" align="center">{$attributes.willpower}</td>
                </tr>
				<tr>
                  <td class="dataTableCell">Clone Limit</td>
                  <td class="dataTableCell">{$cloneSkillPoints}</td>
                  <td class="dataTableCell">Clone Name</td>
                  <td class="dataTableCell" align="center">{$cloneName}</td>
                </tr>
                <tr>
                  <td colspan="4" class="dataTableHeader" style="text-align: center;">Current Training Information</td>
                </tr>
            {*  <tr>
                  <td class="dataTableCell">Training</td>
                  <td colspan="2" style="color: gold; font-weight: bold;" class="dataTableCell">{if $Training}<a style="color: gold; font-weight: bold; text-decoration: none;" href="#s{$TrainingID}">{$Training}</a>{else}None{/if}</td>
                  <td style="text-align: center;" class="dataTableCell">{if $Training}<img alt="Level {$ToLevel}" src="icons/skillsheet/level{$ToLevel}_act.gif" />{else}&nbsp;{/if}</td>
                </tr> *}
				{if $SkillQueue}
				{foreach key=id item=queue from=$SkillQueue}
				{if ($id == 0)}
				<tr>
                  <td class="dataTableCell">Training</td>
                  <td colspan="2" style="color: gold; font-weight: bold;" class="dataTableCell"><a style="color: gold; font-weight: bold; text-decoration: none;" href="#s{$queue.typeID}">{$queue.typeName}</a></td>
                  <td style="text-align: center;" class="dataTableCell"><img alt="Level {$queue.level }" src="icons/skillsheet/level{$queue.level }_act.gif" /></td>
                </tr>				
				{elseif ($id >= 1)}
				<tr>
                  <td class="dataTableCell">In queue</td>
                  <td colspan="2" style="color: gold; font-weight: bold;" class="dataTableCell"><a style="color: gold; font-weight: bold; text-decoration: none;" href="#s{$queue.typeID}">{$queue.typeName}</a></td>
                  <td style="text-align: center;" class="dataTableCell"><img alt="Level {$queue.level }" src="icons/skillsheet/level{$queue.level }_q.gif" /></td>
                </tr>
				{/if}
				{/foreach}
				{/if}
				<tr>
                  <td class="dataTableCell">Queue ends in</td>
				  <td colspan="3" style="text-align: center;" class="dataTableCell">{if $Training}
                      <span class="timerskill">{$SkillQueue.$id.formatetEndTime}</span>{else}&nbsp;{/if}
				  </td>
                <tr>
                  <td colspan="4" class="dataTableCell">&nbsp;</td>
                </tr>
                <tr>
                  <td colspan="4" style="text-align: center; color: gold; font-weight: bold;" class="dataTableCell">This page will be updated in {if $pageupdateminutes eq 0 and $pageupdateseconds eq 0}60{else}{$pageupdateminutes}{/if} minutes and {$pageupdateseconds} seconds.</td>
                </tr>
              </tbody>
              </table>
            </td>
          </tr>
        </tbody>
        </table>
      {foreach item='skillgroup' key='groupid' from=$skilltree}
        {assign var='totalsp' value=0}
        {assign var='countsk' value=0}
        {assign var='groupname' value=$skillgroups.$groupid}
        {*if $groupid eq 256}
          {assign var='groupname' value='Missiles'}
        {/if*}
        <div style="margin-top: 50px; margin-bottom: -24px;">
          <div style="margin-top: 10px;">
            <div style="border-top: 1px solid rgb(67, 67, 67); border-bottom: 1px solid rgb(67, 67, 67); background: rgb(44, 44, 56) no-repeat scroll 74px 5px; margin-bottom: 10px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; height: 21px;"></div>
            <a style="border: 0px none ; color: white; text-decoration: none;" href="http://wiki.eveonline.com/en/wiki/Item_Database:Skills:{$groupname|lower|replace:' ':'_'}"><img alt="{$skillgroups.$groupid}" src="icons/skillsheet/{$groupid}.png" style="border: 0px none ; width: 64px; height: 64px; top: -52px;" class="newsTitleImage" /></a>
            <span style="color: gold; top: -28px;" class="newsTitleText"><strong>{$groupname}</strong>, {$grptable.$groupid.skcount} skills trained, for a total of <strong>{$grptable.$groupid.spcount}</strong> skillPoints.</span><br />
            <div id="{$groupid}" style="margin-left: 82px;">
            {foreach item='skill' key='skillid' from=$skillgroup}
              <div style="border-top: 1px dotted rgb(34, 85, 85);">
                <div style="float: right;margin-top:4px;"><img alt="level{$skill.level}" src="icons/skillsheet/level{if $skill.flag neq 61}{$skill.level}{else}{math equation="x + y" x=$skill.level y=1}_act{/if}.gif" /></div>
                <div style="line-height: 1.45em; font-size: 11px;">
                  <a style="color: white; text-decoration: none;" target="_blank" href="http://wiki.eveonline.com/en/wiki/{$skill.typeName|lower|replace:' ':'_'}" id="s{$skill.typeID}">{$skill.typeName}</a> / <i>Rank {$skill.rank}</i> / <i>SP: {$skill.skillpoints} of {$skill.skilllevel5}</i>
                </div>
                {if $skill.flag eq 61}
                <div>
                  <div style="line-height: 1.5em;margin-left:12px;font-size:11px">
                    <div>
                      <span class="navdot">&#xB7;</span><span style="color:gold;">Currently training to: </span>
                      <strong>Level {math equation="x + y" x=$skill.level y=1}</strong>
                    </div>
                    <div>
                     <span class="navdot">&#xB7;</span><span style="color:gold;">SP done: </span>
                      <strong>{$TrainingSPdone} of {$TrainingDestSP}</strong>
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span style="color:gold;">Started: </span>
                      {$trainingStartTime}
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span style="color:gold;">Ending: </span>
                      {$trainingEndTime}
                    </div>
                    <div>
                      <span class="navdot">&#xB7;</span><span style="color:gold;">Time left: </span>
                      <span class="timerskill">{$trainingEndFormat}</span>
                    </div>
                  </div>
                </div>
                {/if}
              </div>
              {math equation="x + y" x=$totalsp y=$skill.skillpoints assign='totalsp'}
              {math equation="x + y" x=$countsk y=1 assign='countsk'}
            {/foreach}
            </div>
            <div style="line-height: 1.45em; margin-left: 82px; font-size: 11px;">
              <br />
            </div>
          </div>
        </div>
      {/foreach}
        <br />        
        </div>
        </div>
      </td>
    </tr>
    <tr>
      <td><div style="margin: auto; width: 100%; text-align: center;"><br /><br /></div></td>
    </tr>
  </tbody>
  </table>
  <table style="margin: auto auto 20px; width: 70%;" summary="Copyright Info">
  <tbody>
    <tr>
      <td style="text-align: center;">All images and logos are Copyright &copy; <a title="Copyright CCP" href="http://www.ccpgames.com/">CCP</a></td>
    </tr>
  </tbody>
  </table>
{*  
<div id="skillsidebox" style="display:none;" >
   <table style="font-size: 8pt">  
  {foreach item='grp' from=$grptable}
  	<tr>
	 <td><strong>{$grp.grpname}:</strong></td>
	 <td style="text-align: right;">{$grp.spcount}</td>
	</tr>
  {/foreach}
    <tr>
	 <td><strong>Total:</strong></td>
	 <td style="text-align: right;">{$skillpointstotal}</td>
	</tr>
  </table>
</div>
*}
<div id="skillsidebox" style="display:none;" >
  <table>
    <tr>
	 <td>{$l1total}</td>
	 <td>Skills at Level 1</td>
     <td style="text-align: right;">{$l1spsformat} SP</td>
	</tr>
    <tr>
	 <td>{$l2total}</td>
	 <td>Skills at Level 2</td>
     <td style="text-align: right;">{$l2spsformat} SP</td>
	</tr>
    <tr>
	 <td>{$l3total}</td>
	 <td>Skills at Level 3</td>
     <td style="text-align: right;">{$l3spsformat} SP</td>
	</tr>
    <tr>
	 <td>{$l4total}</td>
	 <td>Skills at Level 4</td>
     <td style="text-align: right;">{$l4spsformat} SP</td>
	</tr>
    <tr>
	 <td>{$l5total}</td>
	 <td>Skills at Level 5</td>
     <td style="text-align: right;">{$l5spsformat} SP</td>
	</tr>
	<tr>
	 <td colspan="3">&nbsp;</td>
	</tr>
	<tr>
	 <td>{$totalsks}</td>
	 <td>Skills trained for a total of</td>
     <td style="text-align: right;">{$skillpointstotal} SP</td>
	</tr>
  </table>
</div>