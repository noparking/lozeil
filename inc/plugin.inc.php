<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

function get_plugins_files($ext) {
	$ext_files = array();

	$dir_plugin = directory_for_plugins();

	if (is_dir($dir_plugin) and $ext) {
		if ($handle = opendir($dir_plugin)) {
			while (false !== ($file = readdir($handle))) {
				if (is_dir($dir_plugin.$file) and !preg_match("/^\./", $file)) {
					if ($handle_ext = opendir($dir_plugin.$file)) {
						while (false !== ($file_ext = readdir($handle_ext))) {
							if (preg_match("/^(.*).".$ext."$/", $file_ext)) {
								if (is_directory_for_plugins_external()) {
									$ext_files[] = "../plugins/".$file."/".$file_ext;
								} else {
									$ext_files[] = "plugins/".$file."/".$file_ext;
								}
							}
						}
						closedir($handle_ext);
					}
					if (is_dir($dir_plugin.$file."/medias/".$ext."/")) {
						if ($handle_ext = opendir($dir_plugin.$file."/medias/".$ext."/")) {
							while (false !== ($file_ext = readdir($handle_ext))) {
								if (preg_match("/^(.*).".$ext."$/", $file_ext)) {
									if (is_directory_for_plugins_external()) {
										$ext_files[] = "../plugins/".$file."/medias/".$ext."/".$file_ext;
									} else {
										$ext_files[] = "plugins/".$file."/medias/".$ext."/".$file_ext;
									}
								}
							}
							closedir($handle_ext);
						}
					}
				}
			}
			closedir($handle);
		}
	}

	if ($ext == "js") foreach (Plugins::call_hook('get_js', array()) as $jss) {
		foreach ($jss as $js) {
			$ext_files[] = $js;
		}
	}

	if ($ext == "css") foreach (Plugins::call_hook('get_css', array()) as $csss) {
		foreach ($csss as $css) {
			$ext_files[] = $css;
		}
	}

	return $ext_files;
}
