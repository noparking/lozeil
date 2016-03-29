$(document).ready(function() {
	var timer;
	make_drag_and_drop();
	
	$("body")
		.on("click", "#insert_writings_show", function() {
			$.post(
				"index.php?content=writings.ajax.php",
				({action:"reload_insert_form"}),
				function(data) {
					$.colorbox({html:data});
				}
			);
			return false;
		})
		
		.on("submit", "form[name=\"table_writings_modify\"], form[name=\"table_writings_form_split\"], form[name=\"table_writings_form_forward\"], form[name=\"table_writings_form_duplicate\"]", function() {
			$.post(
				"index.php?content=writings.ajax.php",
				$(this).serialize(),
				function(data) {
					$.colorbox({html:data});
				}
			);
			return false;
		})

		.on("submit", "form[name=\"delete_writing_attachment\"]", function() {
			$.post(
				"index.php?content=writings.ajax.php",
				$(this).serialize(),
				function(data) {
					var result = jQuery.parseJSON(data);
					$(".manage_writing_attachment").replaceWith(result.link);
					show_status(result.status);
				}
			);
			return false;
		})
		
		.on("submit", "form[name=\"table_writings_delete\"]", function() {
			$.post(
				"index.php?content=writings.ajax.php",
				$(this).serialize(),
				function(data) {
					var result = jQuery.parseJSON(data);
					$('#table_writings table').html(result.table);
					make_drag_and_drop();
					refresh_balance();
					show_status(result.status);
				}
			);
			return false;
		})
		
		.on("click", "#table_writings .table_writings_comment", function() {
			$(".table_writings_comment_further_information").slideUp();
			var cell = $(this).find(".table_writings_comment_further_information");
			if (cell.css("display") == "none") {
				cell.slideDown();
			}
			return false;
		})
		
		.on("click", "#table_writings .sort", function() {
			var order_col_name = $(this).attr('id');
			$.post(
				"index.php?content=writings.ajax.php",
				{action: "sort", order_col_name: order_col_name},
				function(data) {
					var result = jQuery.parseJSON(data);
					$('#table_writings table').html(result.table);
					make_drag_and_drop();
					show_status(result.status);
				}
			)
		})
		
		.on("submit", "form[name=\"table_writings_split\"], form[name=\"table_writings_forward\"], form[name=\"table_writings_duplicate\"], form[name=\"table_edit_writings_form\"], form[name=\"insert_writings_form\"]", function() {
			$.colorbox.close();
			$.post(
				"index.php?content=writings.ajax.php",
				$(this).serialize(),
				function(data) {
					var result = jQuery.parseJSON(data);
					$('#table_writings table').html(result.table);
					refresh_balance();
					make_drag_and_drop();
					show_status(result.status);
				}
			)
		return false;
		})
		
		.on("keyup change", "form[name=\"table_writings_split\"] li input", function() {
			clearTimeout(timer);
			var value = $(this).val();
			var id = $(this).closest("form").find("#writing-id").val();
			var type = $(this).attr("id");
			var form = $(this).closest("form").serialize();
			timer = setTimeout(function() {
				$.post(
					"index.php?content=writing.ajax.php",
					{action: "preview_changes_split", value: value, type: type, id: id, form: form},
					function(data) {
						$(".preview_changes").html(data);
					}
				)
			}, 200);
		return false;
		})
		
		.on("keyup change", "form[name=\"table_writings_duplicate\"] li select", function() {
			clearTimeout(timer);
			var value = $(this).val();
			var id = $(this).closest("form").find("#writing-id").val();
			var type = $(this).attr("id");
			var form = $(this).closest("form").serialize();
			timer = setTimeout(function() {
				$.post(
					"index.php?content=writing.ajax.php",
					{action: "preview_changes_duplicate", value: value, type: type, id: id, form: form},
					function(data) {
						$(".preview_changes").html(data);
					}
				)
			}, 200);
		return false;
		})

		.on("keyup change", "form[name=\"table_writings_forward\"] li select", function() {
			clearTimeout(timer);
			var value = $(this).val();
			var id = $(this).closest("form").find("#writing-id").val();
			var type = $(this).attr("id");
			var form = $(this).closest("form").serialize();
			timer = setTimeout(function() {
				$.post(
					"index.php?content=writing.ajax.php",
					{action: "preview_changes_forward", value: value, type: type, id: id, form: form},
					function(data) {
						$(".preview_changes").html(data);
					}
				)
			}, 200);
		return false;
		})
		
		.on("change", "input#amount_excl_vat", function() {
			$(this).val($(this).val().replace(",", "."));
			var amount_inc_vat = Math.round($(this).val() * (($("input#vat").val()/100 +1))*1000000)/1000000;
			$("input#amount_inc_vat").val(amount_inc_vat);
		})

		.on("change", "input#amount_inc_vat", function() {
			$(this).val($(this).val().replace(",", "."));
			var amount_excl_vat = Math.round($(this).val() / (($("input#vat").val()/100 +1))*1000000)/1000000;
			$("input#amount_excl_vat").val(amount_excl_vat);
		})

		.on("change", "input#vat", function() {
			$(this).val($(this).val().replace(",", "."));
			var amount_excl_vat = Math.round($("input#amount_inc_vat").val() / (($(this).val()/100 +1))*1000000)/1000000;
			$("input#amount_excl_vat").val(amount_excl_vat);
		})

		.on("keyup change", "form[name=\"extra_filter_writings_form\"]", function(event) {
			var text = false;
			if (event.target.type.match(/text/)) {
				text = true;
			}
			var input = $(this);
			clearTimeout(timer);
			if (event.type != "change" || !text) {
				timer = setTimeout(function() {
					$.post(
						"index.php?content=writings.ajax.php",
						input.serialize(),
						function(data){
							var result = jQuery.parseJSON(data);
							$('#table_writings table').html(result.table);
							make_drag_and_drop();
							show_status(result.status);
						}
					);
				}, 200);
			}
		})
		
		.on("change", "#filter_accountingcodes_none", function () {
			$(this).closest(".extra_filter_item").find(".input-ajax-content").toggle();
		})
		
		.on("change", "#filter_number_duplicate", function () {
			$(this).closest(".extra_filter_item").find("#filter_number").toggle();
		})
		
		.on("change", ".extra_filter_item input[name=\"filter_accountingcodes_id\"]", function () {
			$(this).closest(".extra_filter_item").find(".field_complement").toggle();
		})

		.on("submit", "form[name=\"extra_filter_writings_form\"]", function() {
			var input = $(this);
			$.post(
				"index.php?content=writings.ajax.php",
				input.serialize(),
				function(data){
					var result = jQuery.parseJSON(data);
					$(".extra_filter_writings").replaceWith(result.extra);
				}
			);
			return false;
		})
		
		.on("mouseenter", "#table_writings tr", function() {
			$(this).find(".operations > div:not(.modify)").css("display", "inline-block");
		})
		
		.on("mouseleave", "#table_writings tr", function() {
			$(this).find(".operations > div:not(.modify)").hide();
		})
		
		.on("change", "select[name='categories_id']", function() {
			var select = $(this);
			$.post(
				"index.php?content=categories.ajax.php",
				{ method: "json", action: "fill_vat", value: $(this).val() },
				function(data){
					var result = jQuery.parseJSON(data);
					select.parent().parent().parent().find("#vat").val(result.data).change();
				}
			);
		})
		
		.on("click", "#extra_filter_writings_toggle", function() {
			$(".extra_filter_writings_days input").each(function() {
				$(this).keyup();
			})
			if ($(".extra_filter_item:first").css("display") == "none") {
				$(".extra_filter_item").slideDown(200);
			} else {
				var flag = 0;
				$(".extra_filter_item").each(function() {
					if ($(this).hasClass("filter_hide")) {
						$(this).removeClass("filter_hide");
						$(this).addClass("filter_show");
						flag = 1;
					}
				})
				if (flag) {
					$(".extra_filter_item").slideDown(200);
				} else {
					$(".extra_filter_item").slideUp(200);
				}
			}
		})
		
		.on("click", "#checkbox_all_up, #checkbox_all_down", function() {
			var $checkboxes = $("#table_writings, #select_writings").find(':checkbox');
			if (this.checked) {
				$checkboxes.each(function() {
					$(this)[0].checked = true
				});
			} else {
				$checkboxes.each(function() {
					$(this)[0].checked = false
				});
			}
		})

		.on("change", ".li-clone", Li_clone)

});

function make_drag_and_drop() {
	$(".dropzone-input").remove();
	$(".droppable").each(function() {
		$(this).dropzone({
			init: function() {
				this.on("success", function(object, data) {
					var result = jQuery.parseJSON(data);
					show_status(result.status);
				})
			},
			url: "index.php?content=writings.ajax.php",
			paramName: $(this).attr('id'),
			fallback : function () {
				return false;
			}
		})
	});
	$("#table_writings tr.draggable").droppable({
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
					var result = jQuery.parseJSON(data);
					$('#table_writings table').replaceWith(result.table);
					make_drag_and_drop();
					show_status(result.status);
				}
			);
		}
	});

	var table_header = $(".table_header").html();
	$("#table_writings tr.draggable").draggable({
		cursor: "pointer",
		stack: "tr",
		helper: function(event) {
			var html = $(this).html();
			var id = $(this).attr('id');
			return "<div class=\"dragged\"><table><tr id=\""+id+"\">"+html+"</tr><tr id=\"table_header_dragged\">"+table_header+"</tr></table></div>";
		}
	});
}

function reload_select_writings() {
	$.ajax({
		type: "POST",
		url : "index.php?content=writings.ajax.php",
		data : {action: "reload_select_writings"}
	}).done(function (data2) {
		$("#select_writings").replaceWith(data2);
	});
}

function confirm_option(text) {
	var select = $("#options_modify_writings");
	
	if (select.val() == 'delete' || select.val() == 'estimate_accounting_code' || select.val() == 'estimate_category') {
		if(confirm(text)) {
			var ids = get_checked_values();
			$.post(
				"index.php?content=writings.ajax.php",
				{action: "form_options", option : $(select).val(), ids : ids},
				function(data) {
					refresh_balance();
					var result = jQuery.parseJSON(data);
					reload_select_writings();
					$('#table_writings table').html(result.table);
					show_status(result.status);
				}
			);
			return false;
		} else {
			return false;
		}
	}
	
	$.post(
		"index.php?content=writings.ajax.php",
		{action: "form_options", option : $(select).val()},
		function(data) {
			$('#form_modify_writings').html(data);
		}
	);
	return false;
}

function get_checked_values() {
	var $checkboxes = $("#table_writings").find('.table_checkbox:checkbox');
	var ids = [];
	$checkboxes.each(function() {
		if ($(this)[0].checked) {
			ids.push($(this).val());
		}
	});
	return JSON.stringify(ids);
}

function confirm_modify(text) {
	if(confirm(text)) {
		var select = $("#options_modify_writings").val();
		var ids = get_checked_values();
		var serialized = $("form[name=\"writings_modify_form\"]").serialize();
		serialized += "&action=writings_modify&ids=" + ids +"&operation=" + select;
		$.post(
			"index.php?content=writings.ajax.php",
			serialized,
			function(data) {
				reload_select_writings();
				refresh_balance();
				var result = jQuery.parseJSON(data);
				$('#table_writings table').html(result.table);
				
				make_drag_and_drop();
				show_status(result.status);
			}
		);
	}
	return false;
}

$(document).ajaxStop(function() {
	changeColorLine();
})

var timercolor;
function changeColorLine(){
	clearTimeout(timercolor);
	timercolor = setTimeout(function(){
		$('#table_writings tr.modified').removeClass('modified');
	},6000);
};


var li_clone_index = 1;

function Li_clone() {
	var hide_label = true;
	var parent_li = $(this).closest("li");
	var cloned_li = parent_li.clone();
	var html = cloned_li.html().replace(/new/g, "new" + li_clone_index);
	cloned_li.html(html);
	parent_li.after(cloned_li);
	if (hide_label) {
		cloned_li.find("label").css("visibility", "collapse");
	}
	$(this).removeClass("li-clone");
	li_clone_index = (parseInt(li_clone_index) + 1);
};
