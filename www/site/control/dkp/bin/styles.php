<?php
$styles = array();

//standard
$style = array();
$style["name"] = "Standard WebDKP";
$style["description"] = "The standard blue and gray table used on WebDKP.com";
$style["createdby"] = "Zedd";
$style["file"] = "standard";
$styles[] = $style;

$style = array();
$style["name"] = "Custom";
$style["description"] = "Select this to build your own style! With a custom style you can directly ".
						"edit the CSS behind the table so it fits in flawlessly with your site. After
						 selecting this option you'll see an button to edit the CSS near the top of the
						 page.";
$style["createdby"] = "You";
$style["file"] = "custom";
$styles[] = $style;

$style = array();
$style["name"] = "Dark Gray";
$style["description"] = "A Dark Gray style that is similar to the tables used on <a href='http://www.wowhead.com'>Wowhead.com</a>.";
$style["createdby"] = "Zedd";
$style["file"] = "darkgray";
$styles[] = $style;


?>