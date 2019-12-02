<?php if(sizeof($nav->list) == 0) { ?>
	<?php if($editPageMode){ ?>
	This navigation part is empty. Please edit it to add links.
	<?php } ?>
	<br />
<?php } else { ?>
<div class="navigationContainer">
<ul class="navigationList">
	<?php foreach($nav->list as $link){ ?>
	<?php if($link->canView()){ ?>
	<li <?=($link->isHere?"class='active'":"")?>><a href="<?=$link->getLink()?>"><?=$link->name?></a></li>
	<?php } ?>
	<?php } ?>
</ul>
</div>

<?php } ?>