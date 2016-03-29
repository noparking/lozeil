var spinner;
$(document)
	.ready(function() {
		
		$('.layout_status').slideDown(400);
		timerstatus = setTimeout(function(){
			$('.layout_status').slideUp(200);
		},3000);
		
		$("body")
			.on("submit", "form[name=\"menu_actions_import_bank_form\"], form[name=\"menu_actions_import_source_form\"]", function() {
				spinner = new Spinner(opts).spin();
				$(".loading").append(spinner.el);
			})
                         
			.on("click", "input.input-ajax-checkbox", function() {
				if ($(this).is(':checked') == false) {
					$(this).parent().hide();
					$(this).parent().parent().parent().children("input:first").show();
				} else {
					$(this).parent().parent().parent().find(".input-ajax-dynamic").hide();
				}
			})
			
			.on("keyup", "input.input-ajax", function(event) {
				if (event.keyCode == 27) {
					$(this).parent().find(".input-ajax-dynamic").hide();
				}
				else {
					input_ajax_input = $(this);
					clearTimeout(input_ajax_timer);
					input_ajax_timer = setTimeout("input_ajax_get()", 200);
				}
			})

	    		.on("keyup", "input.select-ajax", function(event) {
			    if (event.keyCode == 27) {
				$(this).parent().find(".input-ajax-dynamic").hide();
			    }
			    else {
				select_ajax_input = $(this);
				clearTimeout(select_ajax_timer);
				select_ajax_timer = setTimeout("select_ajax_get()", 200);
			    }
			})
	    

			.on("click", "*", function () {
				$(this).find(".select-ajax-dynamic").hide();
				$(this).find(".input-ajax-dynamic").hide();
			})
	
			.find("tr.modified").delay('6000').queue(function(next){
				$(this).removeClass('modified');
			})
	    		
		$("#menu_actions_export").hide();
		
		$(".menu_actions_import_label").nextAll().hide();
		
		$(".menu_handle").on("click", function() {
			if ($(this).hasClass("hide")) {
				$(this).addClass("show").removeClass("hide");
				$(".menu_actions").slideDown(400);
			} else {
				$(this).addClass("hide").removeClass("show");
				$(".menu_actions").slideUp(400);
			}
				$("#menu_handle_hide, #menu_handle_show").toggle();
		})
		
		$("#menu_actions_export_label").on("click", function() {
			$("#menu_actions_export").toggle();
			return false;
		})
		$(".menu_actions_import_label, #menu_actions_other").on("click", function() {
			$(this).nextAll().toggle();
			return false;
		})
		
	        var input_ajax_timer;
	        var input_ajax_input;
	       
	        var select_ajax_timer;
	        var select_ajax_input;

		window.select_ajax_get = function() {
		var input = select_ajax_input;
		$('#'+input.attr("id")+'-dynamic').empty();
		input.addClass("waiting");
		$.getJSON(
			input.attr("data-url"),
			{ method: "json", action: "search", name: input.val(), format: input.attr("data-format") },
			function(data){
				if ($('#'+input.attr("id")+'-dynamic').is(":empty")) {
					$('#'+input.attr("id")+'-dynamic').hide();
				}
				for (i in data) {
					var checkbox = $("<div><input type=\"checkbox\" value=\""+i+"\" name=\""+input.attr("data-name")+"\" /></div>");
					checkbox.append(data[i]);
					$('#'+input.attr("id")+'-dynamic').append(checkbox);
					if ($('#'+input.attr("id")+'-dynamic').is(":visible") == false) {
					    $('#'+input.attr("id")+'-dynamic').show();
					}
					checkbox.click(function() {
						if ($(this).parent().attr("id") == input.attr("id")+'-dynamic') {
						    var id = $(this).children('input').attr('value');
						    var exists = false;
						    $("#" + input.attr("id") + "-static").find("input").each(
							function () {
							    if($(this).attr("value") == id) {
								exists = true;
							    }
							}
						    );
						    if (exists == false) {
							$(this).detach();
							$(this).children("input").attr('checked', "checked");
							$('#'+input.attr("id")+'-static').prepend($(this));
						    }
						    $('#'+input.attr("id")+'-dynamic').hide();
						    $('#'+input.attr("id")+'-dynamic').empty();
						}
					});
				}
			    input.removeClass("waiting");
			}
		);
         	}

		window.input_ajax_get = function() {
		var input = input_ajax_input;
		var input_dynamic = $(input).next();
		var input_static = $(input).next().next();
		$(input_dynamic).empty();
		$(input_dynamic).show();
		input.addClass("waiting");
		$.getJSON(
			input.attr("data-url"),
			{ method: "json", action: "search", name: input.val(), format: input.attr("data-format") },
			function(data){
				for (i in data) {
					var checkbox = $("<div><input type=\"checkbox\" value=\""+i+"\" name=\""+input.attr("data-name")+"\" class=\"input-ajax-checkbox\" /></div>");
					checkbox.append(data[i]);
					$(input_dynamic).append(checkbox);
					checkbox.click(function() {
					    if ($(this).parent().attr("id") == input.attr("id")+'-dynamic') {
							$(this).detach();
							$(this).children("input").attr('checked', "checked");
							$(input_static).html($(this));
							$(input_dynamic).empty();
							input.hide();
						}
					});
				}
				input.removeClass("waiting");
			}
		);
	}
	})
	.keyup(function(e) {
	  if (e.keyCode == 27) {
		  if ($(".menu_handle").hasClass("show")) {
				$(".menu_handle").addClass("hide").removeClass("show");
				$(".menu_actions").slideUp(400, function() {
					$("#menu_handle_show").hide();
					$("#menu_handle_hide").show();
				});
			}
		$("#table_writings .operations input[type='text']").attr("type", "hidden");
		$(".insert_writings_form, .table_writings_comment_further_information").slideUp();
		$("#insert_writings_hide, #insert_writings_cancel").hide();
		$("#insert_writings_show").show();
	  }   
	});

function refresh_balance() {
	$.ajax({
		type: "POST",
		url : "index.php?content=timeline.ajax.php"		
	}).done(function (data) {
		$("#heading_timeline").html(data);
	});
}


var opts = {
  lines: 12, // The number of lines to draw
  length: 5, // The length of each line
  width: 2, // The line thickness
  radius: 6, // The radius of the inner circle
  corners: 1, // Corner roundness (0..1)
  rotate: 0, // The rotation offset
  direction: 1, // 1: clockwise, -1: counterclockwise
  color: '#000', // #rgb or #rrggbb or array of colors
  speed: 2, // Rounds per second
  trail: 60, // Afterglow percentage
  shadow: false, // Whether to render a shadow
  hwaccel: false, // Whether to use hardware acceleration
  className: 'spinner', // The CSS class to assign to the spinner
  zIndex: 2e9, // The z-index (defaults to 2000000000)
  top: "-15", // Top position relative to parent in px
  left: "-15" // Left position relative to parent in px
};

$(document)
	.ajaxStart(function() {
		spinner = new Spinner(opts).spin();
		$(".loading").append(spinner.el);
	})
	.ajaxStop(function() {
		spinner.stop();
	})


var timerstatus;
function show_status(status) {
	$('.layout_status').slideUp(200, function() {
		$(this).empty().html(status);
	})
	clearTimeout(timerstatus);
	$('.layout_status').slideDown(400);
	timerstatus = setTimeout(function(){
		$('.layout_status').slideUp(200);
	},3000);
}
