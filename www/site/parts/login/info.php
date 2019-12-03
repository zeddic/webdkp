<?php
//A user friendly name for this part
$name = 			"Login";
//A short description for this part.
$description = 		"A module that lets users log into the site.";
//who created it
$createdBy = 		"Scott Bailey";
//Is this a system part? System parts are protected and
//are not allowed to be placed on any page by the user
$system = 			0;
// Set some options
$options[] = new partOption("Size", partOption::TYPE_DROPDOWN, "Normal",	"Small,Normal");
$options[] = new partOption("Grab Focus", partOption::TYPE_CHECKBOX, false);

//A list of permissions that must be present for this
//part to function correctly.
//First entry is the name of the permission needed, the second
//entry is the category that the permission falls under.
//$permissions[] = array("Edit HTML", "HTML Part");

//A list of default permission assignments for the above permissions.
//For example, the "Edit X" permission should be given to the Admin.
//Note: You can only grant groups permissions to the permissions specified
//in the permissions array.
//$permissionAssignments[] = array("Edit HTML", "Admin");
//$permissionAssignments[] = array("Edit HTML", "Moderator");
?>