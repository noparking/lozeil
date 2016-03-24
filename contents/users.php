<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2016 */

if (isset($_POST['action']) and isset($_POST['users'])) {
	$current_user = new User();
	$current_user->load(array('id'=> $_SESSION['user_id']));

	foreach ($_POST['users'] as $id => $data) {
		if (isset($data['checked'])) {
			$user = new User();
			$user->load(array('id' => $id));
			if ($user->id > 0) {
				switch ($_POST['action']) {
					case 'delete':
						$user->delete();
						break;
				}
			}
		}
	}
}

$menu = Plugins::factory("Menu_Area");
echo $theme->menu($menu);

$heading = new Heading_Area(utf8_ucfirst(__('manage the users')));
echo $theme->heading($heading);

$user = new User();
$working = $user->link_to_edit();

$users = new Users();
$users->select();
$working .= $users->display();

$area = new Working_Area($working);
echo $area->show();
