<div class="EditPageBorder">
	<div class="EditPageBorderInner<?=($fromTemplate?"Template":"")?>">
		<div class="EditPageBorderHeader<?=($fromTemplate?"Template":"")?>"
			onmouseover="PageEditor.ShowPartButtons(<?=$iid?>)"
			onmouseout="PageEditor.HidePartButtons(<?=$iid?>)">
			<div class="EditPageBorderButtons<?=($fromTemplate?"Template":"")?>" id="EditPageBorderButtonsEdit<?=$iid?>" style="display:none">
				<a href="javascript:PageEditor.SavePartEditor(<?=$iid?>)">Save</a>
				&nbsp;
				<a href="javascript:PageEditor.HidePartEditor(<?=$iid?>)">Cancel</a>
			</div>
			<div class="EditPageBorderButtons<?=($fromTemplate?"Template":"")?>" id="EditPageBorderButtons<?=$iid?>" style="display:none">
				<a href="javascript:PageEditor.TogglePartOptions(<?=$iid?>)">Options</a>
				<?php if($editView != ""){ ?>
				&nbsp;
				<?php if($useAjaxEdit){ ?>
				<a href="javascript:PageEditor.ShowPartEditor(<?=$iid?>,'<?=$editView?>')">Edit</a>
				<?php } else { ?>
				<a href="<?=$PHP_SELF?>?editpage=1&v<?=$iid?>=<?=$editView?>">Edit</a>
				<?php } ?>
				<?php } ?>
				<?php if(!$fromTemplate){ ?>
				&nbsp;
				<a href="javascript:PageEditor.DeletePart(<?=$iid?>)">
					<img src="<?=$theme->getAbsCommonDirectory()?>images/editpage/close.gif">
				</a>
				<?php } ?>
			</div>
			<div class="EditPageBorderTitle<?=($fromTemplate?"Template":"")?> handle" >
				<img class="EditPageBorderIcon" src="<?=$icon?>">
				<span id="EditPageBorderTitle<?=$iid?>">
				<?=$title?>
				</span>
			</div>

		</div>
		<div id="EditPageOptions<?=$iid?>" class="EditPageOptions" style="display:none">
			<?=$options?>
		</div>
		<div class="EditPageBorderContent" id="EditPageSampleContent<?=$iid?>">
		<?=$content?>
		</div>
	</div>
</div>
