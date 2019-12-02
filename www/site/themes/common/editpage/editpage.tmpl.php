<?php
/*==========================================================
The following code is inserted at the bottom of the page while
in edit page mode. When present it will call scriptaculos
to make all the page areas 'sortable lists'. This is what allows
parts to be dragged and arranged within a page.
This will invoke a method in editpage.js (in the js folder)
whenever there is an update to parts posistion, which will
in change make an ajax call.
==========================================================*/
?>

<div id="PartLibrary" style="display:none"></div>

<script type="text/javascript">
	Util.AddOnLoad(PageEditor.Setup);
</script>
