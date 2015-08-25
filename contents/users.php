<?php
  /* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

$menu = Plugins::factory("Menu_Area");
echo $menu->show();
$heading = new Heading_Area(utf8_ucfirst(__('manage the users')));
echo $heading->show();

if (isset($_POST['action']) and isset($_POST['users'])) {
	$current_user = new User();
	$current_user->load(array('id'=> $_SESSION['user_id']));

	foreach ($_POST['users'] as $id => $data) {
		if (isset($data['checked']) or isset($data['submit'])) {
			$user = new User();
			$user->load(array('id' => $id));
			if ($user->id > 0) {
				switch ($_POST['action']) {
				case 'delete':
					$user->delete();
					break;
				case 'viewexpert':
					$user->savemodexpert('1');
					$user->update();
					if ($current_user->id == $user->id) {
						$_SESSION['accountant_view'] = "1";
					}	
					break;
				case 'restricted':
					$user->savemodexpert('0');
					$user->update();
					if ($current_user->id == $user->id) {
						$_SESSION['accountant_view'] = "0";
					}
					break;
				case "save":
					$u =  new User();
					$u->load(array('username' => $data['username']));
					$user->password = "";

					if (empty($user->username)) {
						status(__("user"), __('username empty'), 1);
						break;						
					}

					if ($u->id > 0 and $u->id != $user->id ) {
						status(__("user"), __('username already exists'), 1);
						break;
					}
					
					if (isset($data['password']) and !empty($data['password'])) {
						$user->password = $data['password'];
					}
					if (isset($data['view'])) {
						$user->savemodexpert('1');
						if ($current_user->id == $user->id) {
							$_SESSION['accountant_view'] = "1";
						}
					}
					else {
						$user->savemodexpert('0');
						if ($current_user->id == $user->id) {
							$_SESSION['accountant_view'] = "0";
						}
					}
					$cleaned = $user->clean($data);
					$user->fill($cleaned);
					$user->save();
					break;
				default:;
				}
			}
		}
	}
 }

if(isset($_POST['new_user_name']) and empty($_POST['action'])) {
	$u = new User();
	$u_loaded = new User();
	$u_loaded->load(array("username" =>  $_POST['new_user_username']));
	if ($u_loaded->id == 0) {
		if(isset($_POST['new_user_psw']) and $_POST['new_user_psw'] != "") {
			$u->password = $_POST['new_user_psw'];
		}
		else {
			$u->password = "";
		}
		
		$data['name'] = $_POST['new_user_name'];
		$data['username'] = $_POST['new_user_name'];
		$data['mail'] = $_POST['new_user_mail'];
		$cleaned = $u->clean($data);
		$u->fill($cleaned);
		
		if (!empty($u->username))
			$u->save();
		else
			status(__("user"), __('username empty'), 1);
		$u->savemodexpert(isset($_POST['new_user_view'])?$_POST['new_user_view']:"0");
	}
	else {
		status(__("user"), __("username")." ".__("already exists"), 1);
	}
 }

$users = new Users();

echo $users->add_user();
$users->select();
$working = $users->display();
$area = new Working_Area($working);
echo $area->show();