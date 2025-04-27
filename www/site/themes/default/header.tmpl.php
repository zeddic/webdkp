<div id="header">
	<a href="<?=$SiteRoot?>"><img src="<?=$theme->getAbsDirectory()?>images/header/logo.jpg"></a>
</div>
<div id="bar">
	<a class="barlink" href="<?=$SiteRoot?>">Home</a>
	<div class="barsep">&nbsp;</div>
	<a class="barlink" href="<?=$SiteRoot?>addon">Addon</a>
	<div class="barsep">&nbsp;</div>
	<a class="barlink" href="<?=$SiteRoot?>Browse">Browse</a>
	<div class="barsep">&nbsp;</div>
	<a class="barlink" href="https://github.com/zeddic/webdkp" target="_blank">GitHub</a>
	<div class="barsep">&nbsp;</div>
	<?php if($siteUser->visitor){ ?>
	<a class="barlink" href="<?=$SiteRoot?>join">Join</a>
	<div class="barsep">&nbsp;</div>
	<a class="barlink" href="<?=$SiteRoot?>Login">Login</a>
	<div class="barsep">&nbsp;</div>
	<?php } else { ?>
	<a class="barlink" href="<?=dkpUtil::GetGuildUrl($siteUser->guild)?>">Your DKP</a>
	<div class="barsep">&nbsp;</div>
	<a class="barlink" href="<?=$SiteRoot?>login?siteUserEvent=logout">Logout</a>
	<div class="barsep">&nbsp;</div>
	<?php } ?>
</div>