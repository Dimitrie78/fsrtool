<br/>

<table class="user" cellspacing="0" cellpadding="3">
  <thead>
	<tr>
	  <th>id</th>
	  <th>Name</th>
	  <th>Time (min)</th>
	  <th>last run</th>
	  <th>Description</th>
	  <th>Status</th>
	</tr>
  </thead>
  <tbody>
{foreach $jobs as $k => $v}
	<tr id="cron_{$k}" class="{cycle values="up,down"}">
	  <td>{$v.id}</td>
	  <td>{$v.name}</td>
	  <td>{$v.interwal}</td>
	  <td>{$v.time}</td>
	  <td>{$v.description}</td>
	  <td>{if $v.status == '1'}<img class="cron" id="{$k}" src="icons/tick.png" />{else}<img class="cron" id="{$k}" src="icons/cross.png" />{/if}</td>
	</tr>
{/foreach}
  </tbody>
</table>
