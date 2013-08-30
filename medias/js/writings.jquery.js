var order_direction = 0;
var sort_by = "delay";

function toggle_line_information() {
	$(".table_writings_comment").on("click", function() {
		$(".table_writings_comment_further_information").slideUp();
		var cell = $(this).find(".table_writings_comment_further_information");
		if (cell.css("display") == "none") {
			cell.slideDown();
		} else {
			cell.slideUp();
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
			$(this).removeClass('over').addClass('out');
			$.post(
				"index.php?content=writings.ajax.php",
				{ method: "json", action: "merge", writing_from: writing_from, writing_into: writing_into, sort_by: sort_by, order_direction: order_direction },
				function(data) {
					refresh_balance();
					$('#table_writings table').html(data);
					$("#table_" + writing_into).addClass('over').delay('3000').queue(function(next){
						$(this).removeClass('over');
					})
				}
			);
		}
	});
}

function make_draggable() {
	var table_header = $(".table_header").html();
	var html = "";
	var id = 0;
	$("tr.draggable").draggable({
		cursor: "pointer",
		stack: "tr",
		helper: function(event) {
			html = $(this).html();
			id = $(this).attr('id');
			return "<div class=\"dragged\"><table><tr id=\""+id+"\">"+html+"</tr><tr id=\"table_header_dragged\">"+table_header+"</tr></table></div>";
		}
	});
}

//function make_split() {
//	$(".split").bind("click", function() {
//		var grandparent = $(this).parent().parent();
//		$(".form-split").parent().parent().css({"background-color" : "inherit"});
//		$(".form-split").remove();
//		$(this).after("<form class=\"form-split\"><input type=\"text\"/></form>");
//		var tosplit = grandparent.attr('id');
//		grandparent.css({"background-color" : "#f6f6f6"});
//		$(".form-split").bind("submit", function(event) {
//			event.preventDefault();
//			var amount = $(this).children().val();
//			$.post(
//				"index.php?content=lines.ajax.php",
//				{ method: "json", action: "split", tosplit: tosplit, amount: amount, sort_by: sort_by, order_direction: order_direction },
//				function(data) {
//					$('.content table').html(data);
//				}
//			);
//		})
//	})
//	$("#split").bind("click", function() {
//		$(".form-split").parent().parent().css({"background-color" : "inherit"});
//		$(".form-split").remove();
//	})
//	
//}
//
//
function make_split() {
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

function sort_elements() {
	$(".sort").bind("click", function() {
		order_col_name = $(this).attr('id');
		if (order_direction) {
			order_direction = 0;
			var direction = 'DESC';
		} else {
			order_direction = 1;
			var direction = 'ASC';
		}
		$.post(
			"index.php?content=writings.ajax.php",
			{ method: "json", action: "sort", order_col_name: order_col_name, direction: direction},
			function(data) {
				$('table').html(data);
				if ( order_direction == 0 ) {
					$("#"+order_col_name).addClass("sorteddown");
				} else {
					$("#"+order_col_name).addClass("sortedup");
				}
			}
		)
	})
}

function jQuery_table() {
	make_droppable();
	make_draggable();
	make_split();
	sort_elements();
	toggle_line_information();
}

$(function() {
	jQuery_table();
})

$(document).ajaxStop(function() {
	jQuery_table();
})

$(document).ready(function() {
	$("#extra_filter_writings_value").keyup(function() {
		var word = $("#extra_filter_writings_value").val();
		$.post(
			"index.php?content=writings.ajax.php",
			{ method: "json", action: "filter", value: word },
			function(data){
				$('#table_writings table').html(data);
			}
		);
	});
});

