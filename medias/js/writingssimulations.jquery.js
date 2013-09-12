$(document).ready(function() {
	$("body")
		.on("click", "#edit_writingssimulation_show", function() {
			$(".edit_writingssimulation_form").slideDown(200, function() {
				$("html, body").animate({ scrollTop: $(document).height() }, "slow");
			});
			$(this).hide();
			$("#edit_writingssimulation_hide, #edit_writingssimulation_cancel").show();
		})
		.on("click", "#edit_writingssimulation_hide", function() {
			$(".edit_writingssimulation_form").slideUp(200);
			$(this).hide();
			$("#edit_writingssimulation_show").show();
		})
		//Edition des enregistrements
		.on("click", ".modify a", function() {
			var row = $(this).parent().parent().parent();
			var id = row.attr("id").substr(6);
			if (row.next().hasClass("table_writingssimulation_form_modify")) {
				$("#table_edit_writingssimulation").slideUp(400, function() {
				$(".table_writingssimulation_form_modify").remove();
			})
			} else {
				$.post(
					"index.php?content=writingssimulations.ajax.php",
					{action: "edit", id: id},
					function(data) {
						$(".table_writingssimulation_form_modify").remove();
						$(data).insertAfter(row);
						$("#table_edit_writingssimulation").slideDown();
					}
				);
			}
			return false;
		})
		.on("click", "#table_edit_writings_cancel", function() {
			$("#table_edit_writings").slideUp(400, function() {
				$(".table_writings_form_modify").remove();
			})
			return false;
		})
		
		//Affichage des opérations
		.on("mouseenter", "tr", function() {
			$(this).find(".operations > div").css("display", "inline-block");
		})
		
		.on("mouseleave", "tr", function() {
			$(this).find(".operations > div").hide();
		})
		
		//Toggle input split & duplicate
		.on("click", "input#table_writingssimulation_duplicate_submit", function() {
			var next = "";
			if ($(this).next().val() == "") {
				event.preventDefault();
				if ($(this).next().attr("type") == "hidden") {
					next = "text";
				} else {
					next = "hidden";
				}
			$("input#table_writingssimulation_duplicate_submit").next().attr("type", "hidden");
					$(this).next().attr("type", next);
			}
		})
});
