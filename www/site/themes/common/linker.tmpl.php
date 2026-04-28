<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=$title?></title>
	<link rel="icon" HREF="<?=$SiteRoot?>favicon.ico" type="image/x-icon" />
	<link rel="Shortcut Icon" HREF="<?=$SiteRoot?>favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?=$SiteRoot?>css/main.css?themeid=<?=$theme->id?>" />
	<link rel="stylesheet" type="text/css" href="<?=$SiteRoot?>js/lightbox/css/lightbox.css"  />
	<script src="<?=$SiteRoot?>js/jquery-3.4.1.min.js" type="text/javascript"></script>
 	<script src="<?=$SiteRoot?>js/lightbox/lightbox.js" type="text/javascript"></script> 
 	<script src="<?=$SiteRoot?>js/dkp.js" type="text/javascript"></script>
 	<script src="<?=$SiteRoot?>js/power.js" type="text/javascript"></script>
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-V364S22CZ2"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-V364S22CZ2');
	</script>
 	<?=$extraHeaders?>
</head>
<body>
	<?=$content?>
	<div id="SiteMessage" class="SiteNotice" style="display:none"></div>
	<div id="SiteLoading" class="SiteNotice" style="display:none"><img src="<?=$theme->getAbsCommonDirectory()?>images/loading.gif" style="vertical-align:middle" /> Loading...</div>
</body>
</html>
