$(document).ready(function() {

	var cache = true;

	$("th").each(
	function() {
		$(this).find("#checkbox_all_view").each(
			function() {
				$(this).showMoreAcronym();
			})
		$(this).find(".hidden_field").each(
			function() {
				$data = $(this).attr('class');
				$(this).attr("class", $data + " show_acronym");
			})
	})

	$('.table_checkbox').each(
	function() {
		$(this).find('#checkbox_reporting').each(
			function() {
				$(this).showHideChildren();
			})
		$(this).find('.hidden_field').each(
			function() {
				$data = $(this).attr('class');
				$(this).attr('class', $data + ' show_acronym');
			})
	})

	$("body")
	.on("click", "#checkbox_reporting", function() {			
		$(this).showHideChildren();
		save_checkbox_view();
	})

	.on("click", "#checkbox_all_view", function() {
		$checkboxes = $(this).parent().parent().parent().parent().parent().find(":checkbox");
		if (!$(this).is(":checked")) {
			$checkboxes.each(function() {
				$(this).prop("checked", false);
				if ($(this).attr("id") == "checkbox_reporting") {
					$(this).showHideChildren();
				} else {
					$(this).showMoreAcronym();
				}
			})
			$(this).showMoreAcronym();
		} else {
			$checkboxes.each(function() {
				$(this).prop("checked", true);
				if ($(this).attr("id") == "checkbox_reporting") {
					$(this).showHideChildren();
				} else {
					$(this).showLessAcronym();
				}
			})
			$(this).showLessAcronym();
		}
		save_checkbox_view();
	})
});	

(function($){
	$.fn.showHideChildren = function() {
		$val = $(this).attr("value");
		$here = $("[id=reporting_" + $val + "]");
		$reportings = $("[parent= " + $val + "]");
		$codes = $("[name=sub_table_" + $val + "]");

		if (!$(this).is(":checked")) {
			$reportings.each(function() {
				$(this).find("#checkbox_reporting").prop("checked", false);
				$(this).css("display", "none");
			});

			$codes.each(function() {
				$(this).css("display", "none");
			});

			$(this).showMoreAcronym();

		} else {
			$reportings.each(function() {
				$(this).find("#checkbox_reporting").prop("checked", true);
				$(this).css("display", "table-row");
			})

			if ($codes.length > 0) {
				$codes.each(function() {
					$(this).css("display", "table-row");
				});
			}

			$(this).showLessAcronym();
		}

		if ($codes.length == 0 && $reportings.length == 0) {
			$(this).css('display', 'none');
			$(this).parent().removeAttr('id');
			$codes.each(function() {
				$(this).css("display", "none");
			});
		} 

		$reportings.each(function() {
			$(this).find("#checkbox_reporting").showHideChildren();
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

function save_checkbox_view() {
	var $values = [];
	var $checks = [];
	$(".table_checkbox").each(function() {
		$(this).find("#checkbox_reporting").each(function() {
			$values.push($(this).attr("value"));
			$checks.push($(this).is(':checked'));
		})
	})

	var donnee = {reportingcode: $values, check: $checks, action: "remember_checkbox_status"};
	$.post("index.php?content=balancescustom.ajax.php", donnee, function(data) {});
}
