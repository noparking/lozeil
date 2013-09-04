$(document).ready(function() {
	$('.sparkline-values').sparkline(
		'html',
		{ 
			enableTagOptions:true,
			width: '120px',
			height: '20px',
			barSpacing: '4px',
			barWidth: '5px',
			lineColor:'#666',
			fillColor:'#F5F5FF',
			potColor:'#000',
			minSpotColor:'#ff0000',
			maxSpotColor:'#009900'
		}
	);
});