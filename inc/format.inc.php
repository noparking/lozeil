<?php
/*
	lozeil
	$Author:  $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Format {
	static function page_class($string) {
		$elements = explode("/", $string);
		$string = array_pop($elements);
		$elements = explode(".", $string);
		return "page-".array_shift($elements);
	}
	
	static function body_class($string) {
		$elements = explode(".", $string);
		return "body-".array_shift($elements);
	}
	
	static function text($string, $convert_http = true) {
		$string = htmlspecialchars($string);
		if (strpos($string, "http://") !== false and $convert_http) {
			$string = preg_replace("/(http):\/\/([^[:space:]]*)([[:alnum:]#?\/&=])/","<a href=\"\\1://\\2\\3\">\\1://\\2\\3</a>", $string);
		}
		$string = nl2br($string);
		
		return $string;
	}
	
	static function name($string) {
		if (!trim($string)) {
			$string = "<em>".__('no name')."</em>";
		}
	
		return $string;
	}
	
	static function date_time($timestamp) {
		if ($timestamp == 0) {
			return "";
		}
		
		return date("d/m/Y H:i:s", $timestamp);
	}
	
	static function date($timestamp, $locale = null) {
		if ($timestamp == 0) {
			return "";
		}
		
		if ($locale === null) {
			$locale = $GLOBALS['param']['locale_lang'];
		}
		
		switch ($locale) {
			case "en_US":
				return date("m/d/Y", $timestamp);
			default:
				return date("d/m/Y", $timestamp);
		}
	}
	
	static function date_day($timestamp) {
		if ($timestamp == 0) {
			return "";
		}
		$month = date("F", $timestamp);
		return date("d ", $timestamp).__($month).date(" Y", $timestamp);
	}
	
	static function date_in_full($timestamp) {
		if ($timestamp == 0) {
			return "";
		}
		return $GLOBALS['array_week'][date("w", $timestamp)]." ".date("d", $timestamp)." ".$GLOBALS['array_month'][(int)date("m", $timestamp)]." ".date("Y", $timestamp);
	}

	static function time_start_stop($start, $stop, $day = 0) {
		$time = "";
		if ($day == 0) {
			$day = mktime(0, 0, 0, date("m", $start), date("d", $start), date("Y", $start));
		}
		if ($day != mktime(0, 0, 0, date("m", $start), date("d", $start), date("Y", $start))) {
			$time .= date("d/m/Y", $start)." ";
		}
		if ("00h00" != date("H\hi", $start)) {
			$time .= date("H\hi", $start);
		}
		if ($stop > $start) {
			$time .= " - ";
			if ($day != mktime(0, 0, 0, date("m", $stop), date("d", $stop), date("Y", $stop))) {
				$time .= date("d/m/Y", $stop)." ";
			}
			if ("00h00" != date("H\hi", $stop)) {
				$time .= date("H\hi", $stop);
			}
		}

		return $time;
	}

	static function time($timestamp) {
		if ($timestamp == 0) {
			return "";
		}
		
		return date("H:i:s", $timestamp);
	}

	static function span_input($timestamp, $unit = null, $always_show_unit = false, $precision = 2) {
		if ($timestamp != 0) {
			return self::span($timestamp, $unit, $always_show_unit, $precision);
		} else {
			return "";
		}
	}

	static function span($timestamp, $unit = null, $always_show_unit = false, $precision = 2) {
		if ($unit === null) {
			$unit = $GLOBALS['param']['time_unit'];
		}

		if ($unit == "d") {
			$span = round($timestamp / $GLOBALS['param']['absence_fullday'], $precision);
			if ($always_show_unit) {
				$span .= " ".$GLOBALS['txt_day_'];
			}
		} elseif ($unit == "h-10") {
			$span = round($timestamp / 3600, $precision);
		} else {
			$span = time_format(round($timestamp, 4));
		}
		
		return $span;
	}
	
	static function percentage($rate, $locale = null) {
		return number_format((float)$rate, 2, ",", " ")." %";
	}
	
	static function percentage_colorized($rate, $locale = null) {
		if ($rate > 0) {
			return "<span class=\"green_number\">".self::percentage($rate, $locale)."</span>";
		} elseif ($rate < 0) {
			return "<span class=\"red_number\">".self::percentage($rate, $locale)."</span>";
		} else {
			return self::percentage($rate, $locale);
		}
	}
	
	static function number($number, $precision = 2, $locale = null) {
		if ($locale === null) {
			$locale = $GLOBALS['param']['locale_lang'];
		}

		switch ($locale) {
			case "en_US":
				$result = number_format(abs($number), $precision, ".", ",");
				break;

			case "en_EN":
				$result = number_format(abs($number), $precision, ".", " ");
				break;

			case "fr_NC":
				$result = number_format(abs($number), 0, ",", " ");
				break;

			default:
				$result = number_format(abs($number), $precision, ",", " ");
				break;
		}
		if ($number < 0) {
			$result = "-".$result;
		}

		return $result;
	}

	static function currency_amount($amount, $locale = null, $force_zero = false, $symbol = false) {
		$result = "";
	
		if ($symbol === false or empty($symbol)) {
			$symbol = $GLOBALS['param']['currency'];
		}
		if ($force_zero === true or !empty($amount)) {
			if ($locale === null) {
				$locale = $GLOBALS['param']['locale_lang'];
			}
	
			switch ($locale) {
				case "en_EN":
					if ($amount >= 0) {
						$result = $symbol.number_format($amount, 2, ".", " ");
					} else {
						$result = "-".$symbol.number_format(abs($amount), 2, ".", " ");
					}
					break;
	
				case "fr_NC":
					if ($amount >= 0) {
						$result = number_format($amount, 0)." ".$symbol;
					} else {
						$result = "-".number_format(abs($amount), 0)." ".$symbol;
					}
					break;
	
				default:
					if ($amount >= 0) {
						$result = number_format($amount, 2, ",", " ")." ".$symbol;
					} else {
						$result = "-".number_format(abs($amount), 2, ",", " ")." ".$symbol;
					}
					break;
			}
		}
	
		return $result;
	}

	static function currency_amount_colorized($amount, $locale = null, $force_zero = false, $symbol = false) {
		if ($amount > 0) {
			return "<span class=\"green_number\">".self::currency_amount($amount, $locale, $force_zero, $symbol)."</span>";
		} elseif ($amount < 0) {
			return "<span class=\"red_number\">".self::currency_amount($amount, $locale, $force_zero, $symbol)."</span>";
		} else {
			return self::currency_amount($amount, $locale, $force_zero, $symbol);
		}
	}
}
