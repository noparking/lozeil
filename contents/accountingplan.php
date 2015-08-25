<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();

$heading = new Heading_Area(utf8_ucfirst(__('manage accounting plan')));
echo $heading->show();

$accounting_codes = new Accounting_Codes();
$accounting_codes->select();

$working = $accounting_codes->display();
$area = new Working_Area($working);

$submitimport = new Html_input("import_default",__("import default account plan"), "submit");
$form = "<br><center>".$submitimport->input()."</center><script> var message_confirm = \"".__('are you sure? the previous accounting plan will be deleted')."\" ;</script>";

echo $area->show();
echo $form."<br><br>";