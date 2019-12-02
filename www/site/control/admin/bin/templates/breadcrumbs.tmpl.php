<?php if($breadcrumbs != "") { ?>
<div class="breadcrumbs">
<?php foreach($breadcrumbs as $crumb) { $count++; ?>
	<?php if(sizeof($crumb) > 1) { ?>
		<a href="<?=$crumb[1]?>"><?=$crumb[0]?></a>
	<?php } else { ?>
		<?=$crumb[0]?>
	<?php } ?>
	<?php if($count!=sizeof($breadcrumbs)){ ?> › <?php } ?>
<?php } ?>
</div>
<?php } ?>