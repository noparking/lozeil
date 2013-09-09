function make_editable() {
	$(".table_writings_modify a").on("click", function() {
		var row = $(this).parent().parent().parent();
		var id = row.attr("id").substr(6);
		event.preventDefault();
		if (row.next().hasClass("table_writings_form_modify")) {
			$("#table_edit_writings").slideUp(400, function() {
			$(".table_writings_form_modify").remove();
		})
		} else {
			$.post(
				"index.php?content=writings.ajax.php",
				{action: "edit", id: id},
				function(data) {
					$(".table_writings_form_modify").remove();
					$(data).insertAfter(row);
					$("#table_edit_writings").slideDown();
				}
			);
		}
	})
	
	$("#table_edit_writings_cancel").on("click", function() {
		event.preventDefault();
		$("#table_edit_writings").slideUp(400, function() {
			$(".table_writings_form_modify").remove();
		})
	})
}

function toggle_line_information() {
	$(".table_writings_comment").on("click", function() {
		$(".table_writings_comment_further_information").slideUp();
		var cell = $(this).find(".table_writings_comment_further_information");
		if (cell.css("display") == "none") {
			cell.slideDown();
		}
	})
}

function make_droppable() {
	$("tr.draggable").droppable({
		tolerance : "pointer",
		over: function() {
			$(this).removeClass('out').addClass('over');
        },
        out: function() {
			$(this).removeClass('over').addClass('out');
        },
        drop: function() {
        	var writing_from = $(".ui-draggable-dragging tr").attr('id').substr(6);
			var writing_into = $(this).attr('id').substr(6);
			$.post(
				"index.php?content=writings.ajax.php",
				{action: "merge", writing_from: writing_from, writing_into: writing_into},
				function(data) {
					$('#table_writings table tbody').remove();
					refresh_balance();
					$('#table_writings table').html(data);
					$("#table_" + writing_into).addClass('merged').delay('3000').queue(function(next){
						$(this).removeClass('merged');
					})
				}
			);
		}
	});
}

function make_draggable() {
	var table_header = $(".table_header").html();
	$("tr.draggable").draggable({
		cursor: "pointer",
		stack: "tr",
		helper: function(event) {
			var html = $(this).html();
			var id = $(this).attr('id');
			return "<div class=\"dragged\"><table><tr id=\""+id+"\">"+html+"</tr><tr id=\"table_header_dragged\">"+table_header+"</tr></table></div>";
		}
	});
}

function toggle_input() {
	$("input#table_writings_split_submit, input#table_writings_duplicate_submit").on("click", function() {
		var next = "";
		if ($(this).next().val() == "") {
			event.preventDefault();
			if ($(this).next().attr("type") == "hidden") {
				next = "text";
			} else {
				next = "hidden";
			}
		$("input#table_writings_split_submit, input#table_writings_duplicate_submit").next().attr("type", "hidden");
				$(this).next().attr("type", next);
		}
	})
}

function sort_lines() {
	$(".sort").bind("click", function() {
		order_col_name = $(this).attr('id');
		$.post(
			"index.php?content=writings.ajax.php",
			{action: "sort", order_col_name: order_col_name},
			function(data) {
				$('table').html(data);
			}
		)
	})
}

function jQuery_table() {
	make_droppable();
	make_draggable();
	toggle_input();
	sort_lines();
	toggle_line_information();
	make_editable();
	input();
}

$(function() {
	jQuery_table();
})

$(document).ajaxStop(function() {
	jQuery_table();
})

function input() {
	$("input#amount_excl_vat").on("keyup", function() {
		$(this).val($(this).val().replace(",", "."));
		var amount_inc_vat = Math.round($(this).val() * (($("input#vat").val()/100 +1))*1000000)/1000000;
		$("input#amount_inc_vat").val(amount_inc_vat);
	});
	
	$("input#amount_inc_vat").on("keyup", function() {
		$(this).val($(this).val().replace(",", "."));
		var amount_excl_vat = Math.round($(this).val() / (($("input#vat").val()/100 +1))*1000000)/1000000;
		$("input#amount_excl_vat").val(amount_excl_vat);
	});
	
	$("input#vat").on("keyup", function() {
		$(this).val($(this).val().replace(",", "."));
		var amount_excl_vat = Math.round($("input#amount_inc_vat").val() / (($(this).val()/100 +1))*1000000)/1000000;
		$("input#amount_excl_vat").val(amount_excl_vat);
	});
	
	$("#extra_filter_writings_value").keyup(function() {
		$.post(
			"index.php?content=writings.ajax.php",
			{ method: "json", action: "filter", value: $(this).val() },
			function(data){
				$('#table_writings table').html(data);
			}
		);
	});
};
