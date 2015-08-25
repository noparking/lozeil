<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

echo "<div id=\"error_handling\">";
$heading = new Heading_Area(utf8_ucfirst(__('sorry, page not found')));
echo $heading->show();
	
echo "<a href=\"index.php\">&laquo; ".__('back')."</a>";

if ($GLOBALS['config']['error_handling']) {
	error_handling("E_404", __('sorry, page not found'), $_GET['content'], "", "");
}
echo "</div>";