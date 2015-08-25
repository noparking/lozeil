<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Html_Barchart {
	public $data = null;
	public $width = 950;
	public $domain = "";
	public $max = 0;
	public $next = "";
	public $previous = "";
	
	function __construct($data,$next,$previous) {
		$this->next = link_content("content=followupwritings.php&start=".$next."&scale=histogram");
		$this->previous = link_content("content=followupwritings.php&start=".$previous."&scale=histogram");
		$this->prepare_data($data);
	}
	
	function prepare_data($result) {
		$max = 0;
		$domain = "[";
		$data = array();
		foreach($result as $key => $value) {
			$domain .= "\"".$key."\",";
			$data[] = array("name" => $key , "value" => $value);
			$max = ($max<intval(abs($value)))?intval(abs($value)):$max;
		}
		$domain .= "]";
		$this->max = $max;
		$this->domain = $domain;
		$this->data = json_encode($data);
		$this->width = (count($data) * 35);
	}
		
	function show() {
		return  $this->graph_bar();
	}
		
	function graph_bar()
	{
		$pathjs = $GLOBALS['config']['layout_mediaserver']."medias/js/barchart.js";
				$graph =  <<<HTML
		<style>
		
		.chart text {
		  fill: white;
		  font: 10px sans-serif;
		  text-anchor: middle;
		}
		
		#axis text , #yaxis text {
		  font: 10px sans-serif;
		  fill: black;
		  rotation
		}
		
		#axis path,
		#axis line,
		#yaxis path,
		#yaxis line  {
		  fill: none;
		  stroke: #000;
		  shape-rendering: crispEdges;
		}
		
		.x#axis path {
		  display: none;
		}
		rect{
			opacity: .8;
		}
		rect:hover{
			opacity: 1;
		}
		</style>
		<center><a href="{$this->previous}" ><<</a><svg class="chart"></svg><a href="{$this->next}" >>></a></center><br>
			<script type="text/javascript" >
			     var width = {$this->width},max = {$this->max};
                             var data = {$this->data};
                             var domain = {$this->domain};
			</script>
			<script src="{$pathjs}" type="text/javascript"></script>
HTML;
 return $graph;
	}
	
	
}
