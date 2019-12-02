<?php if(isset($eventResult)){ ?>
<div class="<?=($eventResult?"message":"errorMessage")?>" style="width:569px"><b><?=$eventMessage?></b></div>
<?php } ?>

<table class="controltable" style="width:600px" cellspacing=0>
<caption>
Templates
</caption>
<thead>
<?php if(security::hasAccess("Create Page")){ ?>
	<th colspan=3>
		<img src="<?=$directory?>images/newtemplate.gif">
		<a href="javascript:Util.Toggle('newTemplate');Util.Focus('newTemplateName')">new template</a>
		<div id="newTemplate" style="display:none;color:black">
			<form action="<?=$SiteRoot?>edit/newpage" method="post" style="display:inline">
			<input type="hidden" name="event" value="createTemplate">
			<input id="newTemplateName" name="newFileName" type="text">
			<input type="submit" value="create">
			</form>
		</div>
	</th>
<?php } ?>
</thead>
<tbody>

<?php foreach($templates as $template) { $i++; ?>
<tr class="<?=($i%2==0?'odd':'')?>">
	<td class="leftmost" style="width:5px;height:45px;"><img src="<?=$directory?>images/template.gif"></td>
	<?php if(security::hasAccess("Edit Page")){ ?>
	<td ><a href="<?=$PHP_SELFDIR?>templates/<?=$template->id?>"><?=($template->title!=""?$template->title:$template->url)?></a></td>
	<?php } else { ?>
	<td ><?=($template->title!=""?$template->title:$template->url)?></td>
	<?php } ?>
	<td class="rightmost">
		<?php if(security::hasAccess("Edit Page")){ ?>
		<a href="<?=$PHP_SELFDIR?>templates/<?=$template->id?>" >
			<img class="iconButton" src="<?=$directory?>images/edit.png" title="Edit"></a>
		<?php } ?>
		<?php if(security::hasAccess("Create Page")){ ?>
		<a href="<?=$PHP_SELFDIR?>templates/?event<?=$iid?>=deleteTemplate&templateid<?=$iid?>=<?=$template->id?>" onclick="return confirm('Are you sure that you want \nto delete this template?')">
			<img class="iconButton" src="<?=$directory?>images/delete.png" title="Delete"></a>
		<?php } ?>
	</td>
</tr>
<?php } ?>
</tbody>
</table>


<?php if(isset($eventResult)){?>
<script type="text/javascript">
window.onload = function(){ Util.Flash("FormSaved",4000) };
</script>
<?php } ?>


