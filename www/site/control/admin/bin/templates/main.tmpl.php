<br />
Welcome to the admin panel. 

<?php $count=-1; foreach($categories as $category) { $count++; ?>

<?php if($count%2==0) { ?>
<div class="controlPanelSplitter"></div>
<?php } ?>

<div class="<?=($count%2==0?"controlPanelLeft":"controlPanelRight")?>">
	<table width=100%>
	<tr>
	<td width=50 valign=top><img src="<?=$directory?>images/<?=$category->image?>"></td>
	<td valign=top>
	<h3 class="underline"><?=$category->name?></h3>

	<?php foreach($category->items as $item){ ?>
		<?php if($item->type == controlPanelItem::TYPE_SUBCATEGORY) { ?>
			<span class="controlPanelSubcategory"><?=$item->name?></span><br />
			<?php foreach($item->items as $subitem){ ?>
			<a class="controlPanelLink controlPanelSubcategoryLink" href="<?=$subitem->link?>"><?=$subitem->name?></a><br />
			<?php } ?>
		<?php } else { ?>
			<a class="controlPanelLink" href="<?=$item->link?>"><?=$item->name?></a><br />
		<?php } ?>
	<?php } ?>

	</td>
	</tr>
	</table>
</div>

<?php } ?>



