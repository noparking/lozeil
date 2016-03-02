<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Bayesian_Elements extends Collector  {
	public $filters = null;
	public $elements = array();
	public $table_elements = array();
	
	function __construct($class = null, $table = null, $db = null) {
		if ($class === null) {
			$class = substr(__CLASS__, 0, -1);
		}
		if ($table === null) {
			$table = $GLOBALS['dbconfig']['table_bayesianelements'];
		}
		if ($db === null) {
			$db = new db();
		}
		parent::__construct($class, $table, $db);
	}
	
	function increment_decrement(Writing $writing_before, Writing $writing) {
		if ($writing_before->different_from($writing)) {
			$bayesian_elements = new Bayesian_Elements();
			$bayesian_elements->stuff_with($writing_before);
			$bayesian_elements->decrement();
			
			$bayesian_elements = new Bayesian_Elements();
			$bayesian_elements->stuff_with($writing);
			$bayesian_elements->increment();
		}
	}
	
	function increment() {
		foreach ($this as $bayesian_element) {
			$bayesian_element->increment();
		}
		return true;
	}
	
	function decrement() {
		foreach ($this as $bayesian_element) {
			$bayesian_element->decrement();
		}
		return true;
	}
	
	function stuff_with(Writing $writing) {
		$this->reset();
		$datas = $writing->get_data();
		foreach ($datas['classification_target'] as $table_name => $table_id) {
			if ($table_id > 0) {
				foreach ($datas['classification_data'] as $field => $data) {
					foreach ($data as $value) {
						$bayesian_element = new Bayesian_Element();
						$bayesian_element->table_name = $table_name;
						$bayesian_element->table_id = $table_id;
						$bayesian_element->field = $field;
						$bayesian_element->element = $value;
						$this[] = $bayesian_element;
					}
				}
			}
		}
		return true;
	}
	
	function get_distinct_table_id($table_name) {
		$ids = array();
		$result = $this->db->query("SELECT DISTINCT table_id FROM ".$this->db->config['table_bayesianelements']."
			WHERE table_name = ".$this->db->quote($table_name));
		while ($row = $this->db->fetch_array($result[0])) {
			$ids[] = $row['table_id'];
		}
		return $ids;
	}
	
	function get_accounting_codes_in_use() {
		$accounting_codes = new Accounting_Codes();
		$accounting_codes->id = $this->get_distinct_table_id($GLOBALS['dbconfig']['table_accountingcodes']);
		$accounting_codes->select();
		return $accounting_codes;
	}
	
	function get_categories_in_use() {
		$categories = new Categories();
		$categories->id = $this->get_distinct_table_id($GLOBALS['dbconfig']['table_categories']);
		$categories->select();
		return $categories;
	}
	
	function prepare_id_estimation($table_name) {
		if ($table_name == $GLOBALS['dbconfig']['table_accountingcodes']) {
			$this->table_elements = $this->get_accounting_codes_in_use();
		} elseif ($table_name == $GLOBALS['dbconfig']['table_categories']) {
			$this->table_elements = $this->get_categories_in_use();
		}
		
		$this->filter_with(array('table_name' => $table_name));
		$this->select();
		foreach ($this as $bayesian_element) {
			if (!isset($this->elements[$bayesian_element->table_id])) {
				$this->elements[$bayesian_element->table_id] = array();
			}
			if (!isset($this->elements[$bayesian_element->table_id][$bayesian_element->element])) {
				$this->elements[$bayesian_element->table_id][$bayesian_element->element] = 0;
			}
			$this->elements[$bayesian_element->table_id][$bayesian_element->element] += $bayesian_element->occurrences;
		}
	}
		
	function get_where() {
		$query_where = parent::get_where();
		
		if (isset($this->id) and !empty($this->id)) {
			if (!is_array($this->id)) {
				$this->id = array((int)$this->id);
			}
			$query_where[] = $this->db->config['table_bayesianelements'].".id IN ".array_2_list($this->id);
		}
		if (isset($this->filters['element'])) {
			$query_where[] = $this->db->config['table_bayesianelements'].".element = ".$this->db->quote($this->filters['element']);
		}
		if (isset($this->filters['table_name'])) {
			$query_where[] = $this->db->config['table_bayesianelements'].".table_name = ".$this->db->quote($this->filters['table_name']);
		}
		if (isset($this->filters['field'])) {
			$query_where[] = $this->db->config['table_bayesianelements'].".field = ".$this->db->quote($this->filters['field']);
		}
		if (isset($this->filters['table_id'])) {
			$query_where[] = $this->db->config['table_bayesianelements'].".table_id = ".(int)$this->filters['table_id'];
		}
		if (isset($this->filters['occurrences'])) {
			$query_where[] = $this->db->config['table_bayesianelements'].".occurrences > ".(int)$this->filters['occurrences'];
		}
		
		return $query_where;
	}
	
	function filter_with() {
		$elements = func_get_args();
		foreach ($elements as  $element) {
			foreach ($element as $key => $value) {
				$this->filters[$key] = $value;
			}
		}
	}
		
	function train() {
		$bayesianelement = new Bayesian_Element();
		$bayesianelement->truncateTable();
		$writings = new Writings();
		$writings->select();
		foreach ($writings as $writing) {
			$bayesianelements = new Bayesian_Elements();
			$bayesianelements->stuff_with($writing);
			$bayesianelements->increment();
		}
		return true;
	}
	
	function element_probabilities($element, $table_id) {
		$occurrences = isset($this->elements[$table_id]) ? array_sum($this->elements[$table_id]) : 0;
		$element_occurrence = isset($this->elements[$table_id][$element]) ? $this->elements[$table_id][$element] : 0;
		
		if ($occurrences == 0) {
			return 0;
		} else {
			return $element_occurrence / $occurrences;
		}
	}
	
	function fisher_element_probabilities($element, $table_id) {
		$element_probability = $this->element_probabilities($element, $table_id);
		
		if ($element_probability == 0) {
			return 0;
		} else {
			$sum = 0;
			
			foreach ($this->elements as $table_id => $table_element) {
				$sum += $this->element_probabilities($element, $table_id);
			}
			
			return $element_probability / $sum;
		}
	}
	
	function fisher_element_weighted_probabilities($element, $table_id, $weight = 1.0, $assumed_probability = 0.5) {
		$basic_probability = $this->fisher_element_probabilities($element, $table_id);
		$total = 0;
		
		foreach ($this->elements as $table_element) {
			if (isset($table_element[$element])) {
				$total += $table_element[$element];
			}
		}
		
		return ((($weight * $assumed_probability) + ($total * $basic_probability)) / ($weight + $total));
	}
	
	function fisher_data_probability(Writing $writing, $table_id) {
		$probabilities = 1;
		$data = $writing->get_data();
		
		foreach($data['classification_data']['comment'] as $element) {
			$probabilities *= $this->fisher_element_weighted_probabilities($element, $table_id, $GLOBALS['param']['comment_weight']);
		}
		$probabilities *= $this->fisher_element_weighted_probabilities(number_format($data['classification_data']['amount_inc_vat'][0],6), $table_id, $GLOBALS['param']['amount_inc_vat_weight']);
		
		$fisher_score = - 2 * log($probabilities);
		return $this->inverse_chi2($fisher_score, (count($data['classification_data']['comment']) + 1) * 2);
	}
	
	function inverse_chi2($fisher_score, $length) {
		$m = $fisher_score / 2;
		$sum = exp(-$m);
		$term = $sum;
		for ($i = 1; $i <= floor($length / 2); $i++) {
			$term *= $m / $i;
			$sum += $term;
		}
		return min(array($sum, 1));
	}
	
	function fisher_element_id_estimated(Writing $writing, $table_element_id_default = 0) {
		$threshold = $GLOBALS['param']['fisher_threshold'];
		$probabilities = array();
		$table_element_id_best = $table_element_id_default;
		
		foreach ($this->table_elements as $table_element) {
			$probabilities[$table_element->id] = $this->fisher_data_probability($writing, $table_element->id);
		}
		
		$max = 0;
		foreach ($probabilities as $table_element_id => $probability) {
			if ($probability > $threshold and $probability > $max) {
					$table_element_id_best = $table_element_id;
					$max = $probability;
			}
		}
		
		if (isset($probabilities[$table_element_id_best])) {
			for ($i = 1; $i <= count($probabilities); $i++) {
				if ($i != $table_element_id_best) {
					if (isset($probabilities[$i]) and abs($probabilities[$i] - $max) < 0.2) {
						$table_element_id_best = 0;
					}
				}
			}
		}
		return $table_element_id_best;
	}
}
