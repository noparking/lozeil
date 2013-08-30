<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Config_File {
	public $content = null;
	protected $path = "";
	protected $type = "config";

	function __construct($path, $type="config") {
		$this->path = $path;
		$this->type = $type;
	}

	function __toString() {
		return $this->path;
	}

	function type() {
		return $this->type;
	}

	function get_path() {
		return $this->path;
	}

	function get_directory() {
		return dirname($this->path);
	}

	function free() {
		$this->content = null;
		return $this;
	}

	function read() {
		$this->free();

		$content = file_get_contents($this->path);

		if ($content === false) {
			throw new Exception("Unable to read file ".$this->path);
		} else {
			$this->content = $content;
			return $this;
		}
	}

	function exists() {
		return file_exists($this->path);
	}

	function is_writable() {
		return is_writable($this->path);
	}

	function is_readable() {
		return is_readable($this->path);
	}

	function write() {
		if ($this->content === null) {
			trigger_error("Content of file ".$this->path." must not be null", E_USER_ERROR);
		} else {
			$size = @file_put_contents($this->path, $this->content);

			if ($size === false || $size < strlen($this->content)) {
				 throw new Exception("Unable to write in ".$this->path);
			} else {
				return $this;
			}
		}
	}
	
	function write_value($name, $value) {
		if ($this->is_writable()) {
			foreach (file($this->path) as $line) {
				if (preg_match('|^(\\$[^[]+\\[\''.$name.'\'\\]\s*=\s*")[^"]*(";.*)$|u', $line, $parameters)) {
					$contents[] = $parameters[1].$value.$parameters[2]."\n";
				} else {
					$contents[] = $line;
				}
			}
			$this->content = join("", $contents);

			return $this->write();
		}
		
		return false;
	}

	function copy(config_file $config_file) {
		$this->content = $config_file->read()->content;
		return $this->write();
	}

	function display_as_form_object($key = null, $pattern = "[^\']+", $sort = false) {
		$form = "";

		if ($_SERVER['SCRIPT_FILENAME'] != 'setup.php' && !$GLOBALS['config']['ext_lock']) {
			$form .= '<table cellspacing="1" cellpadding="0" class="configuration">';

			$file_contents = file_get_contents($this->path);

			if ($file_contents !== false) {
				if ($key === null) {
					$key = '[^[]+';
				}

				if (preg_match_all('|^\\$('.$key.')\\[\'('.$pattern.')\'\\]\s*=\s*"([^"]*)";(?:\s*//\s*(([^(\n]+)(?:\(([^,\n]+)[^\n]+)?))?|mu', $file_contents, $parameters, PREG_SET_ORDER)) {
					$sorted_parameters = array();

					foreach ($parameters as & $parameter) {
						$sorted_parameters[] = array_slice($parameter, 1);
					}
					if ($sort) {
						sort($sorted_parameters);
					}

					foreach ($sorted_parameters as & $parameter) {
						$form .= '<tr><td class="key">'.$parameter[0].'</td><td class="sub_key">'.$parameter[1].'</td><td class="value">';

						switch (true) {
							case array_key_exists($parameter[1], $GLOBALS['array_param']):
								$form .= '<select name="'.$this->type().'['.$parameter[0].']['.$parameter[1].']">';

								foreach ($GLOBALS['array_param'][$parameter[1]] as $value => $name) {
									$form .= '<option value="'.$value.'"';

									if ($value == $parameter[2]) {
										$form .= ' selected="selected"';
									}

									$form .= '>' . $name . '</option>';
								}

								$form .= '</select>';
								break;

							case isset($parameter[5]) && preg_match('/^[01] - (?:oui|non)$/', $parameter[5]):
								$form .= '<input type="radio" id="param'.$parameter[1].'" name="'.$this->type().'['.$parameter[0].']['.$parameter[1].']" value="1"';

								if ($GLOBALS[$parameter[0]][$parameter[1]]) {
									$form .= ' checked="checked"';
								}

								$form .= '/><label for="param'.$parameter[1].'">oui</label><input type="radio"  id="notParam'.$parameter[1].'" name="'.$this->type().'['.$parameter[0].']['.$parameter[1].']" value="0"';

								if (!$GLOBALS[$parameter[0]][$parameter[1]]) {
									$form .= ' checked="checked"';
								}

								$form .= '/><label for="notParam'.$parameter[1].'">non</label>';
								break;

							case isset($parameter[5]) && $parameter[5] == 'month':
								$form .= '<select name="'.$this->type().'['.$parameter[0].']['.$parameter[1].']">';

								for($i = 1; $i <= 12; $i++) {
									$form .= '<option value="'.$i.'"';

									if ($i == $GLOBALS[$parameter[0]][$parameter[1]]) {
										$form .= ' selected="selected"';
									}

									$form .= '>'.$GLOBALS['array_month'][$i].'</option>';
								}
								$form .= '</select>';
								break;

							default:
								$form .= '<input type="text" name="'.$this->type().'['.$parameter[0].']['.$parameter[1].']" value="'.utf8_htmlentities($parameter[2]).'" />';
						}

						$form .= '</td><td class="comment">'.(!isset($parameter[3]) ? '' : $parameter[3]).'</td></tr>'."\n";
					}
				}
			}

			$form .= '</table>';
		}

		return $form;
	}
	
	function add($key, $value, $type = null, $comment = "") {
		if ($type === null) {
			$type = $this->type();
		}
		if (!empty($comment)) {
			$comment = "\t\t// ".$comment; 
		}
		$this->read();
		$this->content .= "\n## update ".date("d/m/Y H:i", time())."\n";
		$this->content .= "\$".$type."['".$key."'] = \"".$value."\";".$comment."\n";
		
		return $this->write();
		
	}

	function update($values) {
		if (!$this->is_writable()) {
			return false;
		} else {
			if (!isset($values[$this->type()])) {
				return false;
			} else {
				$values = $values[$this->type()];
				$contents = array();

				foreach (file($this->path) as $line) {
					if (!preg_match('|^\\$([^[]+)\\[\'([^\']+)\'\\]\s*=\s*"([^"]*)"(;.*)$|u', $line, $parameters) || !isset($values[$parameters[1]][$parameters[2]])) {
						$contents[] = $line;
					} else {
						$contents[] = '$'.$parameters[1].'[\''.$parameters[2].'\'] = "'.$values[$parameters[1]][$parameters[2]].'"'.$parameters[4]."\n";
					}
				}

				return file_put_contents($this->path, join("", $contents)) !== false;
			}
		}
	}
	
	function values() {
		if (!$this->is_readable()) {
			return false;
		} else {
			$values = array();
			foreach (file($this->path) as $line) {
				if (preg_match('|^\\$([^[]+)\\[\'([^\']+)\'\\]\s*=\s*"([^"]*)";.*$|u', $line, $parameters)) {
					$values[$parameters[1]][$parameters[2]] = $parameters[3];
				}
			}

			return $values;
		}
	}
	
	function read_value($value) {
		if ($this->is_readable()) {
			$values = array();
			foreach (file($this->path) as $line) {
				if (preg_match('|^\\$'.$this->type.'\\[\''.$value.'\'\\]\s*=\s*"([^"]*)";.*$|u', $line, $parameters)) {
					return $parameters[1];
				}
			}
		}
		
		return false;
	}

	function load_at_global_level() {
		if (!$this->is_readable()) {
			return false;
		} else {
			foreach (file($this->path) as $line) {
				if (preg_match('|^\\$([^[]+)\\[\'([^\']+)\'\\]\s*=\s*"([^"]*)";.*$|u', $line, $parameters)) {
					$GLOBALS[$parameters[1]][$parameters[2]] = $parameters[3];
				}
			}

			return true;
		}
	}
}
