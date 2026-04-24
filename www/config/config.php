<?php
$GLOBALS["DatabaseHost"] = getenv('DB_HOST') ?: "mysql";
$GLOBALS["DatabaseName"] = getenv('DB_NAME') ?: "webdkp_main";
$GLOBALS["DatabaseUsername"] = getenv('DB_USER') ?: "webdkp_admin";
$GLOBALS["DatabasePassword"] = getenv('DB_PASSWORD') ?: "docker";
$GLOBALS["DatabasePrefix"] = getenv('DB_PREFIX') ?: "";
$GLOBALS["TimeZone"] = "America/Los_Angeles";
$GLOBALS["SiteTitle"] = "WebDKP - WoW DKP Tracking, In Game Addon, DKP Hosting";
$GLOBALS["SiteKeywords"] = "WebDKP,Addon,DKP,Dragon, Kill, Points, World of Warcraft, Game, Guild, Hosting, Tracking, Attendance";
$GLOBALS["SiteDescription"] = "WebDKP is a World of Warcraft Addon that allows you to track guild DKP and Attendance. Webdkp.com allows you to host this data and share it will guild members.";
$GLOBALS["SupportedExtensions"] = array("php","phps","aspx","html","htm","js","css");
$GLOBALS["DisableSecurity"] = false;
?>