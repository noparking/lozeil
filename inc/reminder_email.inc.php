<?php
/*
	Lozeil Copyright (C) No Parking 2014 - 2014
*/

class Reminder_Email {
	public $timestamp;
	public $user;
	public $date;
	private $to;
	
	function __construct($user = null, $timestamp = null) {
		$this->user = $user;
		if ($timestamp === null) {
			$timestamp = time();
		}
		$this->timestamp = $timestamp;
		$this->date = __('never');
	}
	
	function to($user) {
		$this->to = $user;	
	}
 
	function date($date) {
		$this->date = $date;	
	}
	
	function send($subject, $body) {
		require_once dirname(__FILE__)."/phpmailer/class.phpmailer.php";
		
		$mail = new PHPMailer();
		if (!empty($GLOBALS['config']['smtp_host'])) {
			$mail->IsSMTP();
			$mail->Host = $GLOBALS['config']['smtp_host'];
			$mail->Port = $GLOBALS['config']['smtp_port'];
			$mail->SMTPAuth = false;
		}
		$mail->CharSet = "utf-8";
		$mail->From = $GLOBALS['param']['email_from'];
		$mail->FromName = $GLOBALS['config']['name'];
		$mail->AddAddress($this->to, $this->user);
		$mail->WordWrap = $GLOBALS['param']['email_wrap'];
		$mail->IsHTML(false);
		$mail->Subject = $subject;
		$mail->Body = $body;
		if ($mail->Send()) {
			status($GLOBALS['status_sent'], "", 1);
			Message::log("Sent an email to ".$this->to);
		} else {
			status($GLOBALS['status_email_err'], "", -1);
			Message::log("Error trying to send an email to ".$this->to);
		}
		$mail->ClearAddresses();
	}

	function replace_body($body) {
		$body = str_replace("APPLICATION_NAME", $GLOBALS['config']['name'], $body);
		$body = str_replace("SOFTNAME", $GLOBALS['config']['name'], $body);
		$body = str_replace("DATE", $this->date, $body);
		$body = str_replace("URL", $this->url(), $body);
		$body = str_replace("CONTACT_HELP", $GLOBALS['param']['contact_help'], $body);
		
		return $body;
	}
	
	function url() {
		return $GLOBALS['config']['root_url']."/accounts/".$this->user."/".link_content("content=writingsimportbank.php");
	}

	function send_message($body = "") {
	  $subject = __('reminder import Lozeil')." : ".$this->user;
	  $body = !empty($body) ? $body : $GLOBALS['GLOBAL$array_email']['request'][2];
		$body = $this->replace_body($body);
		return $this->send($subject, $body);
	}
}
