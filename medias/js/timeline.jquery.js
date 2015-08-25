function make_timeline() {
	var width = $("#cubism_width").text(),
		height = $("#cubism_height").text(),
		start_year = $("#cubism_start_year").text(),
    		start_month = $("#cubism_start_month").text(),
		isleap_year = $("#cubism_isleap_year").text(),
		positive_average = $(".cubism_data_positive_average").text(),
		negative_average = $(".cubism_data_negative_average").text(),
		current_month = $("#cubism_current_month").text(),
		link = []
		
	if (isleap_year.length == 0) {
		isleap_year = 1;
	} else {
		isleap_year = 0;
	}
	
	 $(".cubism_link").each(function() {
		 link.push($(this).text());
	 });

	var context = cubism.context()
		.serverDelay(Date.now() - new Date(parseInt(start_year) + 1, parseInt(start_month) -1 , parseInt(isleap_year), 0, 0, 0, 0))
		.clientDelay(0)
		.step(1000*60*60*8)
		.size(width)
		.stop();

	var data = get_data("");
	
	d3.select("#cubismtimeline").call(function(div) {
		$(".axis").remove();
		$(".horizon").remove();
		
		div.datum(data);
		
		div.append("div")
			.attr("class", "axis")
			.call(context.axis()
				.orient("top")
				.tickFormat(d3.time.format("%m/%Y"))
			);
				
		div.append("div")
			.attr("class", "rule")
			.call(context.rule());
		
		div.append("div")
			.attr("class", "horizon")
			.call(context.horizon()
			.height(height)
			.colors(["#B80000", "#FF3232", "#FF8B8B", "#2fa643", "#bae4b3", "#74c476", "#31a354", "#006d2c"])
			.format(d3.format("r"))
			.extent([negative_average*1.5, positive_average*1.5])
		);
			
	});
	
	$("#cubismtimeline g .tick").eq(current_month - 1).attr("font-weight", "bold").attr("font-size", "12");
	
	$("#cubismtimeline g .tick").on("click", function() {
		window.location = link[$(this).index("#cubismtimeline g .tick")];
	});
	
	$("text").each(function() {
		$(this).attr("x", 45)
	})
			
	context.on("focus", function(i) {
		d3.selectAll("#cubismtimeline .value").style("right", i == null ? null : context.size() - i + "px");
	});

	function get_data(name) {
		var values = []
		values.push(null);
		$(".cubism_data li.cubism_data_row").each(function () {
			var val = $(this).text();
			if (val === "0") {
				val = null;
			}
			values.push(val);
			values.push(val);
			values.push(val);
		})
		return context.metric(function(start, stop, step, callback) {
			callback(null, values);
		}, name);
	}
}

window.onload = function() {
	make_timeline();
}

$(document).ajaxStop(function() {
	make_timeline();
})