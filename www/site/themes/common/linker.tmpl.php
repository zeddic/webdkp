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
  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-6378665-1']);
    _gaq.push(['_trackPageview']);
  
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  
  </script>
 	<?=$extraHeaders?>
</head>
<body>
	<?=$content?>
	<div id="SiteMessage" class="SiteNotice" style="display:none"></div>
	<div id="SiteLoading" class="SiteNotice" style="display:none"><img src="<?=$theme->getAbsCommonDirectory()?>images/loading.gif" style="vertical-align:middle" /> Loading...</div>
</body>
</html>
