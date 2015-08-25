<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Html_Piechart {
	public $data = null;
	public $next = "";
	public $previous = "";
	
	function __construct($data,$next,$previous,$filter,$scale) {
		$this->next = link_content("content=followupwritings.php&start=".$next."&scale=".$scale."&filter=".$filter);
		$this->previous = link_content("content=followupwritings.php&start=".$previous."&scale=".$scale."&filter=".$filter);
		$this->prepare_data($data);
	}
	
	function prepare_data($result) {
		$data = array();
		foreach($result as $key => $value) {
			$data[] = array("name" => $key , "value" => $value);
		}
		$this->data = json_encode($data);
	}
	
	function show() {
		return  $this->graph_bar();
	}
	
	function graph_bar()
	{
		$pathjs =  $GLOBALS['config']['layout_mediaserver']."medias/js/piechart.js";
		$graph =  <<<HTML
		<style>

		.arc path {
		  stroke: #fff;
		  opacity:.8;
		}
		.arc path:hover {
			opacity:1;
		}		
		</style>
		<center><a href="{$this->previous}" ><<</a><svg class="piechart"></svg><a href="{$this->next}" >>></a></center><br>
		<script type="text/javascript">
		       var data = {$this->data};
		</script>
		<script src="{$pathjs}" type="text/javascript"></script>
HTML;
 return $graph;
	}
	
	
}
