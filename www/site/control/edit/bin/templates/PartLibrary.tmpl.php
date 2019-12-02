<div id="PartLibraryHeader" >
	<img id="PartLibraryHeaderClose" src="<?=$theme->getAbsCommonDirectory()?>images/editpage/close.gif" onclick="PageEditor.HidePartLibrary();" title="Close Library">
	Part Library
</div>
<ul id="PartLibraryList">
<?php foreach($partInfo as $part){
if($part->name !="" ){?>
	<li class="partLibraryItem">
		<a 	href="javascript:PageEditor.AddPartToPage(<?=$part->id?>)"
			onmousemove="PageEditor.ShowLibraryPartPreview(event,'<?=$part->screenshotExists()?"<div class=\'partScreen\'><img src=\'".$part->getScreenshot()."\'></div>":""?><?=addslashes($part->description)?>')"
			onmouseout="PageEditor.HideLibraryPartPreview();">
			<span class="name"><?=$part->name?></span>
		</a>
	</li>
<?php }}?>
</ul>
</div></div>
<div class="partLibraryItemDetailsBorder" id="PartLibraryDetail" style="display:none">
<div class="partLibraryItemDetails" id="PartLibraryDetailInner">
</div></div>