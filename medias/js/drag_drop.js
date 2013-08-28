var order_direction = 0;
var sort_by = "delay";

function show_further_information() {
	$(".comment").on("click", function() {
		$(".further_information").slideUp();
		var cell = $(this).find(".further_information");
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
        	var toMerge = $(".ui-draggable-dragging tr").attr('id').substr(6);
			var destination = $(this).attr('id').substr(6);
			$(this).removeClass('over').addClass('out');
			$.post(
				"index.php?content=lines.ajax.php",
				{ method: "json", action: "merge", toMerge: toMerge, destination: destination, sort_by: sort_by, order_direction: order_direction },
				function(data) {
					$('.content table').html(data);
					$("#table_" + destination).addClass('over').delay('3000').queue(function(next){
						$(this).removeClass('over');
						next();
					})
				}
			);
		}
	});
}

function make_draggable() {
	var grid_header = $(".grid_header").html();
	var html = "";
	var id = 0;
	$("tr.draggable").draggable({
		cursor: "pointer",
		stack: "tr",
		helper: function(event) {
			html = $(this).html();
			id = $(this).attr('id');
			return "<div class=\"dragged\"><table><tr id=\""+id+"\">"+html+"</tr><tr id=\"grid_header_dragged\">"+grid_header+"</tr></table></div>";
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
	$("input#split_submit, input#duplicate_submit").on("click", function() {
		var next = "";
		if ($(this).next().val() == "") {
			event.preventDefault();
			if ($(this).next().attr("type") == "hidden") {
				next = "text";
			} else {
				next = "hidden";
			}
		$("input#split_submit, input#duplicate_submit").next().attr("type", "hidden");
				$(this).next().attr("type", next);
		}
	})
}

function sort_elements() {
	$(".sort").bind("click", function() {
		var currentlocation = document.location.search;
		sort_by = $(this).attr('id');
		order_direction = (order_direction == 0) ? 1 : 0;
		$.post(
			"index.php?content=lines.ajax.php",
			{ method: "json", action: "sort", sort_by: sort_by, order_direction: order_direction, currentlocation: currentlocation},
			function(data) {
				$('table').html(data);
				if ( order_direction == 0 ) {
					$("#"+sort_by).addClass("sorteddown");
				} else {
					$("#"+sort_by).addClass("sortedup");
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
	show_further_information();
}

$(function() {
	jQuery_table();
})

$(document).ajaxComplete(function() {
	jQuery_table();
})