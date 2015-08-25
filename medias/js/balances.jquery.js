$(document).ready(function() {
	var timer;
	show_search();

	$("body")
	.on("submit", "form[name=\"form_balance\"], form[name=\"table_balances_modify\"], form[name=\"table_balances_split\"], form[name=\"table_balances_add\"]", function() {
		$.colorbox.close();
		action = $(this).find("input[name=action]").attr("value");
		$.post(
			"index.php?content=balances.ajax.php",
			$(this).serialize(),
			function(data) {
				var result = jQuery.parseJSON(data);
				$('#balance').html(result.table);
				show_status(result.status);
			}
		);
		return false;
	})

	.on("click", "#checkbox_all_up, #checkbox_all_down", function() {
		var $checkboxes = $("#balance").find(':checkbox');
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

	.on("click", "#amount, #ratio", function() {
		if ($(this).attr("id") == "amount") {
			$(this).addClass("split-active");
			$("#ratio").removeClass("split-active");
			$("#input_split").attr("value", "amount");
		} else {
			$(this).addClass("split-active");
			$("#amount").removeClass("split-active");
			$("#input_split").attr("value", "ratio");
		}
		$("form[name=\"table_balances_split\"] li input").myfunction();		
	})

	.on("change", "#action", function() {
		var th = $(this).parent().parent().next();

		var include = th.find("select[name=\"include\"]");
		var ratio = th.find("input[name=\"ratio_input\"]");
		var label = th.find(".ratio_label");

		if ($(this).children(":selected").val() == "affected") {
			include.show();
			ratio.hide();
			label.hide();
		} else if ($(this).children(":selected").val() == "split") {
			include.hide();
			ratio.show()
			label.show();
		} else {
			include.hide();
			ratio.hide();
			label.hide();
		}
	})

	.on("keyup change", "form[name=\"table_balances_split\"] li input", function() {
		$(this).myfunction();
	})

	.on("keyup change", "form[name=\"filter_balances\"]", function(event) {
		var value = $(this).find("#search_filter").val();
		$.post(
			"index.php?content=balances.ajax.php",
			{ action: "search", value: value },
			function(data) {
				var result = jQuery.parseJSON(data);
				$("#balance").html(result.table);
				show_search();
				show_status(result.status);
			}
		);
		return false;
	})
});

(function($) {
	$.fn.myfunction = function() {
		var value = $(this).val();
		var id = $(this).closest("form").find("#balance_id").val();
		var type = $(this).attr('id');
		var form = $(this).closest("form").serialize();
		$.post(
			"index.php?content=balances.ajax.php",
			{action: "preview_changes", value: value, type: type, balance_id: id, form: form},
			function(donnee) {
				var result = jQuery.parseJSON(donnee);
				$(".preview_changes").html(result.data);
			}
		)
		return false;
	};
})(jQuery);

function show_search() {
	var table = $("#balance table td");
	var filter = $("#balance table").attr("filter");
	var reg = new RegExp(filter, 'g');
	table.each(function() {
		var name = $(this).attr("name");
		if (name == "number" || name == "name" || name == "amount") {
			var new_text = $(this).html().replace(reg, "<span style=\"color:red;\">" + filter + "</span>");
			$(this).html(new_text);
		}
	})
}

function form_insert(id) {
	var donnee = {balance_id: id, action: "form_insert"};
	$.post(
		"index.php?content=balances.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			$.colorbox({html: result.data});
		}
	);
	return false;
}

function form_split(id) {
	var donnee = {balance_id: id, action: "form_split"};
	$.post(
		"index.php?content=balances.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			$.colorbox({html: result.data});
		}
	);
	return false;
}

function delete_balance(id) {
	var donnee = {balance_id: id, action: "delete"};
	$.post(
		"index.php?content=balances.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			$("#balance").replaceWith(result.table);
			show_status(result.status);
		}
	);
}

$(document).ajaxStop(function() {
	changeColorLine();
})

var timercolor;
function changeColorLine(){
	clearTimeout(timercolor);
	timercolor = setTimeout(function(){
		$('#balance tr.modified').removeClass('modified');
	},6000);
};
