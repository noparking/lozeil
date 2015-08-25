<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Param_File extends Config_File {
	function __construct($path) {
		parent::__construct($path, "param");
	}

	function clean($key, $variable) {
		$bool = array(
			"ext_treasury",
			"ext_simulation",
			"ext_account_custom_result",
			"ext_api",
			"accountant_view",
		);
		$number = array(
			"nb_max_writings",
			"comment_weight",
			"amount_inc_vat_weight",
			"threshold",
			"fisher_threshold", 
			"email_wrap",
			"smtp_port",
			"fiscal year begin",
			"nb default activities",
		);
		$select = array(
			"locale_timezone",
			"locale_lang",
			"currency",
		);

		if (isset($variable)) {
			$cleaned = strip_tags($variable);
			$cleaned = trim(preg_replace("/\s+/", " ", $cleaned));			
		}

		if (in_array($key, $bool)) {
			if ($cleaned != "1")
				$cleaned = "0";
		}

		if (in_array($key, $number))
			$cleaned = intval($cleaned);

		return $cleaned;
	}

	function change_param_value($value = "", Param_file $file_fallback = null) {
		if ($this->exists()) {
			$default_value = $this->find_default_value($value);
		}
		
		if (!isset($default_value) or !$default_value) {
			if ($file_fallback->exists()) {
				$default_value = $file_fallback->find_default_value($value);
			}
		}
		if (!isset($default_value) or !$default_value) {
			echo $value." : ".__("No default value").$default_value."\n";
			$final_value = $this->input("");
			return $final_value;
		} else {
			echo $value." : ".__("Default value :").$default_value."\n".__("Change ? (y/n)");
			while(empty($answer)) {
				$answer = $this->input('');
			};
			if ($answer == "y") {
				while(empty($answer_yes)) {
					$answer_yes = $this->input('');
				};
			} else {
				$answer_yes = $default_value;
			}
			return $answer_yes;
		}			
	}
	
	function overwrite_file(Param_file $dist_config_file = null) {
		if ($this->exists()) {
			echo utf8_ucfirst(__("param file already exists, do you want to overwrite? (y/n)"))."\n";
			while(empty($config_answer)) {
				$config_answer = $this->input("");
			};
		} else {
			$config_answer = "y";
		}
		
		if ($config_answer == "y") {
			if (!$dist_config_file->exists()) {
				die("Configuration file '".$dist_config_file."' does not exist");
			} else {
				try {
					$this->copy($dist_config_file);
					return true;
				} catch (exception $exception) {
					die($exception->getMessage());
				}
			}
		}
		return false;
	}
	
	private function input($message) {
	  fwrite(STDOUT, "$message: ");
	  $input = trim(fgets(STDIN));
	  return $input;
	}

	function grid_header() {
		$grid = array(
			'header' => array(
				'cells' => array(
					array(
						'type' => "th",
						'value' => ucfirst(__("action"))
					),
					array(
						'type' => "th",
						'value' => ucfirst(__("value"))
					),
					array(
						'type' => "th",
						'value' => ucfirst(__("operation"))
					)
				)
			)
		);
		return $grid;
	}
		
	
	function grid_body($params) {
		$params_number = 0;

		if (is_array($params)) {
			foreach ($params as $name => $field) {
				$params_number++;
				$grid[$name] =  array(
					'cells' => array(
						array(
							'type' => "td",
							'value' => ucfirst(__($name)),
						),
						array(
							'type' => "td",
							'value' => $field,
						),
						array(
							'type' => "td",
							'value' => $this->show_operations($name),
						)
					)
				);
			}
		}
		$grid[] = array(
			'class' => "table_total",
			'cells' => array(
				array(
					'colspan' => "2",
					'type' => "th",
					'value' => "",
				),
				array(
					'type' => "th",
					'value' => ucfirst(__('number of parameters')).': '.$params_number,
				),
			),
		);
		return $grid;
	}
	
	function grid($params) {
		return $this->grid_header() + $this->grid_body($params);
	}
	
	function show($params) {
		$html_table = new Html_table(array('lines' => $this->grid($params)));
		return $html_table->show();
	}

	function display($params) {
		return "<div id=\"table_account\">".$this->show_form($params)."</div>";
	}
	
	function show_form($params) {
		return $this->show($params).$this->show_reset();
	}

	function show_reset() {
		$reset = new Html_Input("submit", __("reset to default values"), "submit");
		$reset->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__("are you sure?"))."')"
		);

		return "<form id=\"form_params\" method=\"POST\" action=\"\" name=\"params_id\" >".$reset->input()."</form>";
	}

	function choice_form($name, $value) {
		$bool = array(
			"ext_treasury",
			"ext_simulation",
			"ext_account_custom_result",
			"ext_api",
			"accountant_view",
		);
		$number = array(
			"nb_max_writings",
			"comment_weight",
			"amount_inc_vat_weight",
			"threshold",
			"fisher_threshold", 
			"email_wrap",
			"smtp_port",
			"fiscal year begin",
			"nb default activities",
		);
		$text = array(
			"email_from",
			"smtp_host",
			"contact_help",
		);
		$select = array(
			"locale_timezone",
			"locale_lang",
			"currency",
		);

		if (in_array($name, $bool)) {
			return $this->show_form_bool($name, $value);
		} elseif (in_array($name, $number)) {
			return $this->show_form_number($name, $value);
		} elseif (in_array($name, $text)) {
			return $this->show_form_text($name, $value);
		} elseif (in_array($name, $select)) {
			return $this->show_form_select($name, $value);
		} else {
			return false;
		}
	}

	function show_form_bool($name, $value) {
		$input_radio = new Html_Radio("account[".$name."][name]", array(__("disable"),__("enable")), $value);
		$input_submit = new Html_Input("account[submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify').' '.$name."</h3><form name=\"\" id=\"form_modif_account\"  method=\"post\"  action=\"".link_content("content=account.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('pick a value'))." : </td><td>".$input_radio->item("")."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		return $form;
	}

	function show_form_number($name, $value) {
		$input_number = new Html_Input("account[".$name."][name]", $value, "number");
		$input_submit = new Html_Input("account[submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify').' '.$name."</h3><form name=\"\" id=\"form_modif_account\"  method=\"post\"  action=\"".link_content("content=account.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('enter a number'))." : </td><td>".$input_number->input()."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		return $form;
	}

	function show_form_text($name, $value) {
		$input_text = new Html_Input("account[".$name."][name]", $value, "text");
		$input_submit = new Html_Input("account[submit]",__('modify'),"submit");
		$action = new Html_Input("action","save","hidden");
		$form = "<div class=\"\"><center><h3>".__('modify').' '.$name."</h3><form name=\"\" id=\"form_modif_account\"  method=\"post\"  action=\"".link_content("content=account.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__('enter a text'))." : </td><td>".$input_text->input()."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		return $form;
	}

	function show_form_select($name, $value) {
		$options_timezone = array(
			'none' => "--",
			'Europe/Paris' => ucfirst(__("europe/Paris")),
			'Europe/London' => ucfirst(__("europe/London")),
		);
		$options_lang = array(
			'none' => "--",
			'fr_FR' => ucfirst(__("french")),
			'en_EN' => ucfirst(__("english")),
			'nl_BE' => ucfirst(__("dutch")),
		);
		$options_currency = array(
			'none' => "--",
			'€' => ucfirst(__("Euro €")),
			'£' => ucfirst(__("GBP £")),
		);

		if ($name === "locale_timezone") {
			$chosen_options = $options_timezone;
		} elseif ($name === "locale_lang") {
			$chosen_options = $options_lang;
		} else {
			$chosen_options = $options_currency;
		}

		$input_select = new Html_Select("account[".$name."][name]", $chosen_options, $value);
		$input_submit = new Html_Input("account[submit]", __("modify"), "submit");
		$action = new Html_Input("action", "save", "hidden");
		
		$form = "<div class=\"\"><center><h3>".__('modify').' '.$name."</h3><form name=\"\" id=\"form_modif_account\"  method=\"post\"  action=\"".link_content("content=account.php")."\"><table>";
		$form .= "<tr><td>".ucfirst(__("select a value"))." : </td><td>".$input_select->item("")."</td></tr>";
		$form .= "<tr><td>".$action->input().$input_submit->input()."</td></tr>";
		$form .= "</table></form></center></div><br><br>";
		
		return $form;
	}

	function show_form_modify($name) {
		$form = "<div class=\"modify show_acronym\">
					<span class=\"operation\"> <input class=\"modif\" type=\"button\" id=\"".$name."\" /> </span> <br />
				<span class=\"acronym\">".__('modify')."</span>
				</div>";
		return $form;
	}

	function show_form_reset($name) {
		$input_hidden_id = new Html_Input("table_account_reset_name", $name);
		$input_hidden_action = new Html_Input("reset", $name);
		$submit = new Html_Input("", '',"submit");
		$submit->properties = array(
			'onclick' => "javascript:return confirm('".utf8_ucfirst(__('are you sure?'))."')"
		);
		$form = "<div class=\"reset show_acronym\" >
						<form method=\"post\" name=\"table_account_form_reset\" action=\"\" enctype=\"multipart/form-data\">".
							$input_hidden_action->input_hidden().$submit->input()."
						</form>
						<span class=\"acronym\">".__('reset')."</span>
					</div>";
		return $form;
	}

	function show_operations($name) {
		return $this->show_form_modify($name).$this->show_form_reset($name);
	}
}
