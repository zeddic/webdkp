<?php
/*===========================================================
CSS/Main is a control file that automattically generates
the css file for the page. It does this by combining all of the
css files specified by the current theme as well as all the
css files by the common theme directory. All these css files
are combined into one and returned by this page. By doing this
we limit the number of http requests to the server as well
as allow the placement of php in theme css files to aid in their
generation.
============================================================*/

//check to see if css data was requested for a particular theme
include_once("core/main/cssCache.php");
cssCache::render();
?>