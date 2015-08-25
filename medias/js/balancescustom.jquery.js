$(document).ready(function() {
	var cache = true;
	reload_view();
	make_drag_and_drop();

	$("body")
	.on("click", "#checkbox_view", function() {
		$(this).showHideChildren();
		save_checkbox_view();
	})

	.on("click", "#checkbox_all_view", function() {
		$checkboxes = $("body #checkbox_view");
		if (!$(this).is(":checked")) {
			$checkboxes.each(function() {
				$(this).prop("checked", false);
				$(this).showHideChildren();
			})
			$(this).showMoreAcronym();
		} else {
			$checkboxes.each(function() {
				$(this).prop("checked", true);
				$(this).showHideChildren();
			})
			$(this).showLessAcronym();
		}
		save_checkbox_view();
	})

	.on("click", "#checkbox_all_reporting", function() {
		var $checkboxes = $("[name=reporting], [class=table_checkbox_header]").find(':checkbox');
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

	.on("click", "#checkbox_all_accountingcode", function() {
		$val = $(this).val();
		var $checkboxes = $("[name=account_" + $val + "]").find(':checkbox');
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

	.on("submit", "form[name=\"form_reporting\"]", function() {
		var ids = get_reportings_checked();
		var serialized = $(this).serialize();
		serialized += "&checkbox_reporting=" + ids;
		$.post(
			"index.php?content=balancescustom.ajax.php",
			serialized,
			function(data) {
				var result = jQuery.parseJSON(data);
				$('#master table').html(result.table);
				reload_view();
				make_drag_and_drop();
				show_status(result.status);
			}
		);
		return false;
	})

	.on("submit", "form[name=\"form_accountingcode\"]", function() {
		var ids = get_accountingcodes_checked();
		var serialized = $(this).serialize();
		serialized += "&checkbox_accountingcode=" + ids;
		$.post(
			"index.php?content=balancescustom.ajax.php",
			serialized,
			function(data) {
				var result = jQuery.parseJSON(data);
				$('#master table').html(result.table);
				reload_view();
				make_drag_and_drop();
				show_status(result.status);
			}
		);
		return false;
	})

	.on("submit", "form[name=\"table_reportings_edit\"], form[name=\"table_reportings_add\"], form[name=\"table_accountingcodes_non_affected_edit\"], form[name=\"table_accountingcodes_edit\"]", function() {
		$.colorbox.close();
		action = $(this).find("input[name=action]").attr("value");
		$.post(
			"index.php?content=balancescustom.ajax.php",
			$(this).serialize(),
			function(data) {
				var result = jQuery.parseJSON(data);
				$('#master table').html(result.table);
				reload_view();
				make_drag_and_drop();
				show_status(result.status);
			}
		);
		return false;
	})

	.on("change", "#action", function() {
		$data = $(this).parent().parent().parent().parent().attr("data");
		$include = $(".include_into_reporting_" + $data);
		$ratio = $(".ratio_number_" + $data);
		
		if ($(this).children(':selected').val() == 'include') {
			$include.css("display", "inline-block");
		} else {
			$include.css("display", "none");			
		}

		if ($(this).children(':selected').val() == 'change ratio') {
			$ratio.css("display", "inline-block");
		} else {
			$ratio.css("display", "none");
		}

	})	
});

$(document).ajaxStop(function() {
	changeColorLine();
})

function reload_view() {
	$('#checkbox_all_view').each(function() {
		$(this).showHideChildren();
	});

	$(".hidden_field").each(function() {
		$data = $(this).attr('class');
		$(this).attr("class", $data + " show_acronym");
	});

	$('.op').each(function() {
		$(this).find('#checkbox_view').each(
		function() {
			$(this).showHideChildren();
		})
	});
}

function save_checkbox_view() {
	var $values = [];
	var $checks = [];
	$(".op").each(function() {
		$(this).find("#checkbox_view").each(function() {
			$values.push($(this).attr("value"));
			$checks.push($(this).is(':checked'));
		})
	})

	var donnee = {reportingcode: $values, check: $checks, action: "remember_checkbox_status"};
	$.post("index.php?content=balancescustom.ajax.php", donnee, function(data) {});
}

function make_drag_and_drop() {
	$('.draggable').draggable({opacity: 0.7, helper: 'clone', cursor: 'pointer' ,containment : '#master'});
	$('.droppable').draggable({opacity: 0.7, helper: 'clone', cursor: 'pointer' ,containment : '#master'});
	$('.draggable').droppable({hoverClass: 'hoveringoverdraggable'});
	$('.droppable').droppable({hoverClass: 'hoveringoverdroppable'});
	$('.non_affected').droppable({hoverClass: 'hoveringovernonaffected'});

	$('.draggable').on({
		dragstart: function(e) {
			$drag = $(this);
		}
	});

	$('.droppable').on({
		dragstart: function(e) {
			$drag = $(this);
		}
	});

	$("#master tr.droppable").droppable({
		drop: function(event, ui) {
			if ($drag.attr("class").indexOf('reporting') > -1 ) {
				if ($drag.attr('id') != 'other_accountingcodes') {
					$(this).after($drag);
					$drag.after($('tr[name="account_' +  $drag.attr('value') + '"]'));
					order_reporting($drag.attr('value'), $(this).attr('value'));
				}
			} else {
				$drag.attr("name","account_" + $(this).attr("value"));
				if ($(this).attr('value') != 0) {
					if ($drag.attr("data") != 0) {
						var donnee = {reportingcode: $(this).attr("value"), accountingcode: $drag.attr("value"), data: $drag.attr("data"), action:"dragdrop_affected"};
					} else {
						var donnee = {reportingcode: $(this).attr("value"), accountingcode: $drag.attr("value"), action:"dragdrop_non_affected"};
					}
				}
				$.post(
					"index.php?content=balancescustom.ajax.php",
					donnee,
					function(data) {
						var result = jQuery.parseJSON(data);
						$("#master table").replaceWith(result.table);
						reload_view();
						make_drag_and_drop();
					}
				);
			}
		}
	});

	$("#master tr.draggable").droppable({
        drop: function() {
        	$drag.attr("name","account_" + $(this).attr("data"));
			if ($(this).attr('data') != 0) {
				if ($drag.attr("data") != 0) {
					var donnee = { reportingcode:  $(this).attr("data"), accountingcode: $drag.attr("value") , data: $drag.attr("data"), action:"dragdrop_affected"};
				} else {
					var donnee = { reportingcode:  $(this).attr("data"), accountingcode: $drag.attr("value") , action:"dragdrop_non_affected"};				
				}
			}
			$.post(
				"index.php?content=balancescustom.ajax.php",
				donnee,
				function(data) {
					var result = jQuery.parseJSON(data);
					$("#master table").replaceWith(result.table);
					reload_view();
					make_drag_and_drop();
				}
			);
		}
	});
}

function get_reportings_checked() {
	var $checkboxes = $("#master").find('.table_checkbox_reporting :checkbox');
	var ids = [];
	$checkboxes.each(function() {
		if ($(this)[0].checked) {
			ids.push($(this).val());
		}
	});
	return ids;
}

function get_accountingcodes_checked() {
	var $checkboxes = $("#master").find('.table_checkbox_accountingcode :checkbox');
	var ids = [];
	$checkboxes.each(function() {
		if ($(this)[0].checked) {
			ids.push($(this).val() + "/" + $(this).attr("data"));
		}
	});
	return ids;
}

function order_reporting (id,previous) {
	var donnee = { reportingcode: id, action:'order' , id_previous: previous };
	$.post(
		"index.php?content=balancescustom.ajax.php",
		donnee,
		function(data) {}
	);
}


function delete_reporting (id) {
	var sur =  confirm("Êtes-vous sûr ?");
	if (sur) {
	var donnee = { reportingcode: id, action:'delete'};
	$.post(
	"index.php?content=balancescustom.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			$("#master table").replaceWith(result.table);
			reload_view();
			make_drag_and_drop();
		}
	);
	}
	return false;
}

function delete_accounting(id_account, id) {
	var donnee = { reportingcode: id, accountingcode: id_account,  action:'delete_accounting'};
	$.post(
		"index.php?content=balancescustom.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			$("#master table").replaceWith(result.table);
			reload_view();
			make_drag_and_drop();
		}
	);
}

function form_edit(id) {
	var donnee = { reportingcode: id, action:'form_edit_reporting' };
	$.post(
	"index.php?content=balancescustom.ajax.php",
	donnee,
	function(data) {
		var result = jQuery.parseJSON(data);
		$.colorbox({html: result.data});
	}
	);
	return false;
}

function form_add(id) {
	var donnee = { reportingcode: id, action:'form_add_reporting' };
	$.post(
	"index.php?content=balancescustom.ajax.php",
	donnee,
	function(data) {
		var result = jQuery.parseJSON(data);
		$.colorbox({html: result.data});
	}
	);
}

function form_edit_accountingcode_non_affected(id) {
	var donnee = { reportingcode: 0, action:'form_edit_accountingcode_non_affected',accountingcodes_id:id };
	$.post(
	"index.php?content=balancescustom.ajax.php",
	donnee,
	function(data) {
		var result = jQuery.parseJSON(data);
		$.colorbox({html:result.data});
	}
	);
}

function form_edit_accountingcode(id, reporting) {
	var donnee = { reportingcode: reporting, action:'form_edit_accountingcode',accountingcodes_id:id };
	$.post(
	"index.php?content=balancescustom.ajax.php",
	donnee,
	function(data) {
		var result = jQuery.parseJSON(data);
		$.colorbox({html:result.data});
	}
	);
}

function includeintoreporting(who , where ) {
	var donnee = { reportingcode: who, where: where , action:'include' };
	if ( who !=  where ) {
	$.post(
		"index.php?content=balancescustom.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
		}
	);
	}
}

function change_reporting(reporting_id, accountingcode_id) {
	alert(reporting_id);
	alert(accountingcode_id);
	var donnee = { reportingcode: reporting_id, accountingcode: accountingcode_id, action: "change_reporting" };
	$.post(
		"index.php?content=balancescustom.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
		}
		);
}

var timercolor;
function changeColorLine(){
	clearTimeout(timercolor);
	timercolor = setTimeout(function(){
		$('#master tr.modified').removeClass('modified');
	},6000);
};


(function($) {
	$.fn.showHideChildren = function() {
		$val = $(this).attr("value");
		$here = $("[id=reporting_" + $val + "]");
		$reportings = $("[parent= " + $val + "]");
		$codes = $("[name=account_" + $val + "]");

		if (!$(this).is(":checked")) {
			$reportings.each(function() {
				$(this).find("#checkbox_view").prop("checked", false);
				$(this).css("display", "none");
			});

			$codes.each(function() {
				$(this).css("display", "none");
			});

			$(this).showMoreAcronym();

		} else {
			$reportings.each(function() {
				$(this).find("#checkbox_view").prop("checked", true);
				$(this).css("display", "table-row");
			})

			if ($codes.length > 1) {
				$codes.each(function() {
					$(this).css("display", "table-row");
				});
			}

			$(this).showLessAcronym();
		}

		if ($codes.length == 1 && $reportings.length == 0) {
			$(this).css('display', 'none');
			$(this).parent().removeAttr('id');
			$codes.each(function() {
				$(this).css("display", "none");
			});
		} else if ($codes.length == 1 && $reportings.length > 0) {
			$codes.each(function() {
				$(this).css("display", "none");
			});
		}

		$reportings.each(function() {
			$(this).find("#checkbox_view").showHideChildren();
		});

		return this;
	};

	$.fn.showLessAcronym = function() {
		$(this).parent().attr('id', 'checkbox_less');
		$status = this.attr('less');
		this.parent().next().text($status);

		return this;
	};

	$.fn.showMoreAcronym = function() {
		$(this).parent().attr('id', 'checkbox_more');
		$status = this.attr('more');
		this.parent().next().text($status); 	

		return this;
	};
})(jQuery);
