<?php
/*===========================================================
CLASS FRAMEWORK
=============================================================
The framework class is a utility class that gives access
access to runtime options for the framework.
*/

class framework {
	/*===========================================================
	MEMBER VARIABLES
	============================================================*/

	/*===========================================================
	DEFAULT CONSTRUCTOR
	============================================================*/
	static function defaultOptions() {
		$GLOBALS["Framework_UseTemplateIndents"] = true;
	}

	/*===========================================================
	useTemplateIndents($state)
	Sets whether to use template indenting. (enabled by default).
	This will indent templates within each other, making sorce code
	look neater.
	For example:
	Template 1 Content
		Template 2 Content
			Template 3 Content

	However, this feature can cause problems with text areas for
	certain pages and can be disabled at runtime.

	Accepts:
	State - true or false, controlling whether to use this feature
	============================================================*/
	static function useTemplateIndents($state)
	{
		$GLOBALS["Framework_UseTemplateIndents"] = $state;
	}


	/*===========================================================
	Returns a configuration setting as set in the configuration global
	in config.php
	============================================================*/
	static function getConfigValue($configName){
		$value = $GLOBALS[$configName];
		return $value;
	}
}
framework::defaultOptions();
?>