<div id="PartLoading<?=$iid?>" style="float:right;display:none"><img src="<?=theme::getAbsCommonDirectory()?>images/editpage/loading.gif"></div>

<div style="float:left">Title</div><br />
<input id="EditPageBorderTitleInput<?=$iid?>" onchange="PageEditor.UpdateTitle(<?=$iid?>)" onkeypress="if(Util.IsEnterEvent(event)){PageEditor.UpdateTitle(<?=$iid?>)}" style="width:140px" value="<?=$title?>"><br />

<div style="float:left">Border</div><br />
<?php for($i=0;$i<=$numberOfBorders;$i++){ ?>
	<div id="EditPageBorderSample<?=$iid?>_<?=$i?>" class="<?=($i==$border?"borderSampleChoosen":"borderSamplePadding")?>" style="float:left;width:27px;">
	<div class="borderSample border<?=$i?>Sample" onclick="PageEditor.ChangeBorder(<?=$iid?>,<?=$i?>)">&nbsp;</div>
	</div>
<?php } ?>

<div style="clear:both"></div>

<?php foreach($availableOptions as $availableOption){ ?>
<div style="float:left"><?=$availableOption->name?></div><br />
<?php if($availableOption->type == partOption::TYPE_TEXT){ ?>
<input id="EditPageCustomOption<?=$iid?>_<?=$availableOption->id?>" onchange="PageEditor.UpdateCustomOption(<?=$iid?>,<?=$availableOption->id?>)" onkeypress="if(Util:IsEnterEvent(event)){PageEditor.UpdateCustomOption(<?=$iid?>,<?=$availableOption->id?>)}" style="width:140px" value="<?=$currentOptions[$availableOption->name]?>"><br />
<?php } else if ($availableOption->type == partOption::TYPE_CHECKBOX) { ?>
<input type="checkbox" style="border:0px" id="EditPageCustomOption<?=$iid?>_<?=$availableOption->id?>" onclick="PageEditor.UpdateCustomOption(<?=$iid?>,<?=$availableOption->id?>)" <?=($currentOptions[$availableOption->name]!=""&&$currentOptions[$availableOption->name]!="false"&&$currentOptions[$availableOption->name]!="0"?"checked":"")?>>
<?php } else if ($availableOption->type == partOption::TYPE_DROPDOWN) { ?>
<select id="EditPageCustomOption<?=$iid?>_<?=$availableOption->id?>" onchange="PageEditor.UpdateCustomOption(<?=$iid?>,<?=$availableOption->id?>)">
	<?php foreach($availableOption->choices as $choice){ ?>
	<option <?=($currentOptions[$availableOption->name]==$choice?"selected":"")?>><?=$choice?></option>
	<?php } ?>
</select>
<?php } ?>
<br />
<?php } ?>