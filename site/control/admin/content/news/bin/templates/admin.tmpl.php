<input  class="mediumButton" type="button" value="Write New Post!" onclick="document.location='<?=$PHP_SELF?>?view=edit'" />
<br />
<br />

<div class="noticeBox" id="postResult" style="display:none">
	<?=$eventMessage?>
</div>

<div class="editTabs">
	<ul >
		<li class="selected" id="UnpublishedTab"><a onclick="NewsAdmin.ShowTab('Unpublished')"><span>Drafts</span></a></li>
		<li class="back" id="PublishedTab"><a onclick="NewsAdmin.ShowTab('Published')"><span>Published</span></a></li>
	</ul>
	<div class="editTabsUnderline" style="width:100%"></div>
</div>
<div id="UnpublishedArea">
	<div  class="tabPageLinks" style="width:100%">
		<div style="float:right" id="unpublishedPageLinks"></div>
		<img src="<?=$directory?>images/loading.gif" id="unp_loading" style="display:none">
		Page <span id="unp_current"></span> of <span id="unp_max"></span></b>
	</div>

	<div id="unpublishedContent"></div>
</div>

<div id="PublishedArea" style="display:none">
	<div class="tabPageLinks" style="width:100%">
		<div style="float:right" id="publishedPageLinks"></div>
		<img src="<?=$directory?>images/loading.gif" id="p_loading" style="display:none">
		Page <span id="p_current"></span> of <span id="p_max"></span></b>
	</div>

	<div id="publishedContent"></div>
</div>


<script type="text/javascript">
	NewsAdmin.SetPublishedData(<?=$publishedPage?>,<?=$publishedMaxPage?>);
	NewsAdmin.SetUnpublishedData(<?=$unpublishedPage?>,<?=$unpublishedMaxPage?>);
	NewsAdmin.Init();
	NewsAdmin.ShowTab('<?=$activeTab?>');
</script>