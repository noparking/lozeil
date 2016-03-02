<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

function is_online() {
	return false;
}

function prepare_email_request($to_user_ids, $request_id) {
	$db = new db();
	$list_emailing = array();

	if ($GLOBALS['param']['email_auto']) {
		$request = new Request($request_id);
		$request->load_max();

		$user = new User($request->user_id);

		$user_ids = array();
		$assigned_ids = unserialize($request->assigned_id);
		$referer_ids = unserialize($request->referer_id);
		if (is_array($assigned_ids)) {
			foreach($assigned_ids as $clef_user) {
				if (!in_array($clef_user, $user_ids) and $clef_user != $to_user_ids) {
					$user_ids[] = $clef_user;
				}
			}
		}
		if (is_array($referer_ids)) {
			foreach($referer_ids as $clef_user) {
				if (!in_array($clef_user, $user_ids) and $clef_user != $to_user_ids) {
					$user_ids[] = $clef_user;
				}
			}
		}
		if (is_array($user_ids)) {
			$user_IN = array_2_list($user_ids);

			$query_user = "SELECT ".$db->config['table_users'].".name as user_name, ".
			$db->config['table_users'].".email as email".
			" FROM ".$db->config['table_users'].
			" WHERE ".$db->config['table_users'].".id IN ".$user_IN;

			$result_user = $db->query($query_user);
			if ($result_user[1] > 0) {
				while ($row_user = $db->fetch_array($result_user[0])) {
					$emailing = array();
					$emailing['To'] = $row_user['email'];
					$emailing['ToName'] = $row_user['user_name'];
					$emailing['Subject'] = $GLOBALS['array_email']['request'][0].$request->titre.".";
					$emailing['Body'] = $GLOBALS['array_email']['request'][1];
					$email_replace['SOFTNAME'] = $GLOBALS['config']['name'];
					$email_replace['REQUEST'] = $request->titre;
					$email_replace['USER_NAME'] = $user->name();
					$email_replace['URL'] = $GLOBALS['config']['root_url']."/?content=request.php&request_encours=".$request->request_id;
					$list_emailing[] = prepare_email($emailing, $email_replace);
				}
			}
		}
	}
	return $list_emailing;
}

function get_email_address_admin() {
	$emails_list = array();
	$emails_admin = explode(" ", $GLOBALS['param']['email_admin']);
	if (is_array($emails_admin)) {
		foreach ($emails_admin as $email_admin) {
			$email_admin = trim($email_admin);
			if (is_email($email_admin)) {
				$emails_list[] = $email_admin;
			}
		}
	}
	$emails_list = array_unique($emails_list);

	return $emails_list;
}

function prepare_email($content, $replace="") {
	$prepared_email['From'] = $GLOBALS['param']['email_from'];
	if (isset($content['From']) and is_email($content['From'])) {
		$prepared_email['From'] = $content['From'];
	}
	$prepared_email['FromName'] = $GLOBALS['config']['name'];
	if (isset($content['FromName']) and $content['FromName'] != "") {
		$prepared_email['FromName'] = $content['FromName'];
	}
	if(!isset($content['To'])) {
		$content['To'] = "";
	}
	$prepared_email['To'] = $content['To'];
	if(!isset($content['ToName'])) {
		$content['ToName'] = "";
	}
	$prepared_email['ToName'] = $content['ToName'];
	if(!isset($content['Subject'])) {
		$content['Subject'] = "";
	}
	$prepared_email['Subject'] = $content['Subject'];
	$prepared_email['Body'] = $content['Body'];

	if (is_array($replace)) {
		foreach ($replace as $replace_name => $replace_value) {
			$prepared_email['Body'] = str_replace($replace_name, $replace_value, $prepared_email['Body']);
		}
	}

	return $prepared_email;
}

function email_send($list_emailing, $show="", $preview="") {
	require_once(dirname(__FILE__)."/../inc/phpmailer/class.phpmailer.php");

	$email_status = 0;
	$link_status = "";
	if (sizeof($list_emailing) > 0) {
		if ($show) {
			$email_status = "<table>";
			$email_status .= "<tr>\n";
			$email_status .= "<th>".__('recipient')."</th>\n";
			$email_status .= "<th>".__('txt_subject')."</th>\n";
			$email_status .= "<th>".__('txt_body')."</th>\n";
			$email_status .= "</tr>\n";
		}
		foreach ($list_emailing as $emailing) {
			if (is_email($emailing['To']) and is_email($emailing['From'])) {
				$mail = new phpmailer();
				if (!empty($GLOBALS['config']['email_smtp'])) {
					$mail->IsSMTP();
					$mail->Host = $GLOBALS['config']['email_smtp'];
					$mail->SMTPAuth = false;
				}
				$mail->CharSet = "utf-8";
				$mail->From = $emailing['From'];
				$mail->FromName = isset($emailing['FromName']) ? $emailing['FromName'] : "";
				$mail->AddAddress($emailing['To'], $emailing['ToName']);
				$mail->WordWrap = isset($GLOBALS['param']['email_wrap']) ? $GLOBALS['param']['email_wrap'] : 80;
				$mail->IsHTML(false);
				$mail->Subject = $emailing['Subject'];
				$mail->Body = $emailing['Body'];
				if (isset($emailing['AddAttachment']) and $emailing['AddAttachment']) {
					if (is_array($emailing['AddAttachment'])) {
						foreach ($emailing['AddAttachment'] as $name => $file) {
							$mail->AddAttachment($file, $name);
							Message::log("Email has attachment: '".$file."' => '".$name."'");
						}
					} else {
						$mail->AddAttachment($emailing['AddAttachment']);
						Message::log("Email has attachment: '".$emailing['AddAttachment']."'");
					}
				}

				if (!$preview) {
					if(!$mail->Send()) {
						Message::log("Error try to send an email to ".$emailing['To'].": ".$mail->ErrorInfo);
						$email_status = 0;
						$link_status = status(__('email'), __('email error'), -1);
					} else {
						Message::log("Sent an email to ".$emailing['To']);
						$email_status = 1;
						$link_status = status(__("email"), __("email sent"), 1);
					}
				}

				if ($show) {
					$email_status .= "<tr><td><a href=\"mailto:".$emailing['To']."\">".$emailing['ToName']."</a></td>\n";
					$email_status .= "<td><strong>".format_text($emailing['Subject'])."</strong></td>\n";
					if ($emailing['AddAttachment']) {
						$email_status .= "<td>".format_text($emailing['Body'])."<br />\n";

						if (is_array($emailing['AddAttachment'])) {
							foreach ($emailing['AddAttachment'] as $name => $file) {
								$email_status .= "<cite>".$name.": ".$file."</cite></td></tr>\n";
							}
						} else {
							$email_status .= "<cite>".$emailing['AddAttachment']."</cite></td></tr>\n";
						}
					} else {
						$email_status .= "<td>".format_text($emailing['Body'])."</td></tr>\n";
					}
				}

				$mail->ClearAddresses();
			} else {
				$link_status = status(__('email'), __('email error'), -1);

				if ($show) {
					$email_status .= "<tr><td><a href=\"mailto:".$emailing['To']."\">".$emailing['ToName']."</a></td>\n";
					$email_status .= "<td><strong>".__('email error')."</strong></td>\n";
					$email_status .= ($emailing['To'])?"<td>".$emailing['To']."</td></tr>\n":"<td>--</td></tr>\n";
				}
			}
		}
		if ($show) {
			$email_status .= "</table>";
		}
	}
	return array($link_status, $email_status);
}

function email_auto($content, $content_id="", $content_name="", $to_user_ids="", $body = "") {
	$db = new db();
	$list_emailing = array();

	switch($content) {
		
		default:
			$show = 0;
			if ($GLOBALS['param']['email_auto']) {
				// do nothing
			}
			break;
	}
	email_send($list_emailing, $show);
}
