<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

$writings = new Writings();
echo $writings->show_timeline_at($_SESSION['filter']['start']);

exit(0);
