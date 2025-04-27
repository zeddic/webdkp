<div class="editTabs">
	<ul>
		<li class="<?=($active=="dkp"?"selected":"")?>"><a href="<?=$baseurl?>"><span>DKP</span></a></li>
		<li class="<?=($active=="loot"?"selected":"")?>"><a href="<?=$baseurl?>Loot"><span>Loot</span></a></li>
 		<li class="<?=($active=="awards"?"selected":"")?>"><a href="<?=$baseurl?>Awards"><span>Awards</span></a></li>
		<!--<li class="<?=($active=="sets"?"selected":"")?>"><a  href="<?=$baseurl?>Sets"><span>Sets</span></a></li> -->
		<?php if($settings->GetLootTableEnabled()){ ?>
		<li class="<?=($active=="loottable"?"selected":"")?>"><a  href="<?=$baseurl?>LootTable"><span>Loot Table</span></a></li>
		<?php } ?>
		<?php if($siteUser->guild ?? null == $guild->id || $siteUser->usergroup->name == "Admin") { ?>
		<li class="<?=($active=="admin"?"selected":"")?>"><a  href="<?=$baseurl?>Admin"><span>Admin</span></a></li>
		<?php } ?>
	</ul>
	<div class="editTabsUnderline"></div>
</div>