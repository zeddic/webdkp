<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">

<br />
<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/world.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Instructions</div>
	Remote WebDKP allows you to place an interactive DKP table on your own website.
	This table allows you to view and sort the current DKP for players from all
	tables. You can even style the table so it fits in with the rest of your site.
	Remote WebDKP, however, only shows basic DKP. For more detailed
	records, such as player history or award history, visitors will need to go
	WebDKP.com.
	<br />
	<br />
	<input style="width:100px" type="button" class="largeButton" value="Preview" onclick="window.open('<?=$baseurl?>RemotePreview','WebDKPPreview','width=600,height=400,toolbar=yes,scrollbars=yes,resizable=yes')">
	<br />
	<br />
	To add Remote DKP to your site, please add the following code anywhere on
	your page. A special note to forum users and some Guild hosting sites; the
	text must be placed as HTML inside your page. Some forums and hosting sites
	do not allow this. If your hosting provider does not allow you to add custom
	HTML to pages you <b>cannot</b> use Remote WebDKP.
	<br />
	<br />
	<b>Copy This</b><br />
	<textarea style="width:100%;height:50px;font-size:90%;padding:5px;border:1px solid #BBBDFF;background:#EDEDFF;"><script type="text/javascript" src="http://www.webdkp.com<?=$baseurl?>remote.js"></script>
<div id="webdkp"></div></textarea>
	<br /><br />
</div>

<br />

<div class="adminSectionImage"><img src="<?=$siteRoot?>images/dkp/style.gif"></div>
<div class="adminSection" style="padding-left:2px">
	<div class="title">Style Your Table</div>
	Once you have the DKP Table showing on your site, you can style it by visiting the
	style page.
	<br />
	<br />
	<input type="button" class="largeButton" value="Style Table!" onclick="document.location='<?=$baseurl?>Admin/RemoteStyle'">
</div>

<br />
<br />
<br />

</div>
