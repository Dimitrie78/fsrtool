{if !$checkVer}
<p>
Update from: {$instver} --&gt; {$version}
</p>
<p><a href="?step={$nextstep}">Start Update --&gt;</a></p>
{else}
<p><a href="../index.php">Done</a></p>
{/if}