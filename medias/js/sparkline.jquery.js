$(document).ready(function() {
	var year = $("#year option[selected='selected']").val();
	$('.sparkline-values').sparkline(
		'html',
		{ 
			enableTagOptions:true,
			tooltipFormat: '{{offset:names}} {{y}}',
			tooltipValueLookups: {
				 names: {
				0: '01/' + year,
                1: '02/' + year,
                2: '03/' + year,
                3: '04/' + year,
                4: '05/' + year,
                5: '06/' + year,
                6: '07/' + year,
                7: '08/' + year,
                8: '09/' + year,
                9: '10/' + year,
                10: '11/' + year,
                11: '12/' + year
				 }
			},
			width: '120px',
			height: '20px',
			barSpacing: '4px',
			barWidth: '5px',
			lineColor:'#666',
			fillColor:'#F5F5FF',
			spotColor:'#000',
			minSpotColor:'#ff0000',
			maxSpotColor:'#009900'
		}
	);
});