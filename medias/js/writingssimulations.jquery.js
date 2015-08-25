$(document).ready(function() {
	$("body")
		.on("click", "#insert_simulations_show", function() {
			$.post(
				"index.php?content=writingssimulations.ajax.php",
				({action:"reload_insert_form"}),
				function(data) {
					$.colorbox({html:data});
				}
			);
			return false;
		})
		
		.on("submit", "form[name=\"table_simulations_modify\"], form[name=\"table_simulations_form_split\"], form[name=\"table_simulations_form_forward\"], form[name=\"table_simulations_form_duplicate\"]", function() {
			$.post(
				"index.php?content=writingssimulations.ajax.php",
				$(this).serialize(),
				function(data) {
					$.colorbox({html:data});
				}
			);
			return false;
		})
		
		.on("submit", "form[name=\"table_simulations_delete\"]", function() {
			$.post(
				"index.php?content=writingssimulations.ajax.php",
				$(this).serialize(),
				function(data) {
					var result = jQuery.parseJSON(data);
					$('#table_simulations table').html(result.table);
					refresh_simulations_timeline();
					show_status(result.status);
				}
			);
			return false;
		})
		
		.on("submit", "form[name=\"table_simulations_duplicate\"], form[name=\"edit_simulations_form\"], form[name=\"table_edit_writingssimulation_form\"]", function() {
			$.colorbox.close();
			$.post(
				"index.php?content=writingssimulations.ajax.php",
				$(this).serialize(),
				function(data) {
					var result = jQuery.parseJSON(data);
					$('#table_simulations table').html(result.table);
					refresh_simulations_timeline();
					show_status(result.status);
				}
			)
		return false;
		})
		
		//Affichage des opérations
		.on("mouseenter", "tr", function() {
			$(this).find(".operations > div:not(.modify)").css("display", "inline-block");
		})
		
		.on("mouseleave", "tr", function() {
			$(this).find(".operations > div:not(.modify)").hide();
		})
		
		// Toggle de l'input pour l'évolution
		.on("change", "#evolution", function() {
			if($(this).val() == "linear") {
				$("#evolution_periodical").show();
			} else {
				$("#evolution_periodical").hide();
			}
		})
});

function refresh_simulations_timeline() {
	$.ajax({
		type: "POST",
		url : "index.php?content=writingssimulations.ajax.php",
		data : {action: "refresh_simulations_timeline"}
	}).done(function (data) {
		$("#heading_timeline").html(data);
	});
}