<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

echo "<div id=\"error_handling\">";
$heading = new Heading_Area(utf8_ucfirst(__('sorry, access denied')));
echo $heading->show();
	
echo "<a href=\"index.php\">&laquo; ".__('back')."</a>";
echo "</div>";