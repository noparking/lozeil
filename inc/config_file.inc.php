<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

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
			//chmod($this->path, 0775);

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
	
	function find_default_value($var = "") {
		if (!$this->is_readable()) {
			return false;
		} else {
			foreach (file($this->path) as $line) {
				if (preg_match('|^\\$([^[]+)\\[\'([^\']+)\'\\]\s*=\s*"([^"]*)";.*$|u', $line, $parameters)) {
					if ($parameters[1] == $this->type and $parameters[2] == $var) {
						return $parameters[3]; 
					}
				}
			}

			return false;
		}
	}
	
	function change_config_value($value = "", Config_File $file_fallback = null) {
		if ($this->exists()) {
			$default_value = $this->find_default_value($value);
		}
		if (!isset($default_value) or !$default_value) {
			if ($file_fallback->exists()) {
				$default_value = $file_fallback->find_default_value($value);
			}
		}
		if (!isset($default_value) or !$default_value) {
			echo $value." : ".__('No default value').$default_value."\n";
			$final_value = $this->input('');
			return $final_value;
		} else {
			echo $value." : ".__('Default value :').$default_value."\n".__('Change ? (y/n)');
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
	
	function overwrite(Config_File $dist_config_file = null) {
		if ($this->exists()) {
			echo utf8_ucfirst(__('config file already exists, do you want to overwrite? (y/n)'))."\n";
			while(empty($config_answer)) {
				$config_answer = $this->input('');
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
}
