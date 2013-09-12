$(document)
	.ready(function() {
		$("body").find(".modified").delay('6000').queue(function(next){
			$(this).removeClass('modified');
		})
		
		$("#menu_actions_export").hide();
		$("#menu_actions_import_label").nextAll().hide();
		if($(".edit_writings_form #id").attr("value") > 0) {
			$(".edit_writings_form, #edit_writings_hide, #edit_writings_cancel").show();
			$("#edit_writings_show").hide();
		}
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
		$("#edit_writings_show").on("click", function() {
			$(".edit_writings_form").slideDown(1, function() {
				$("html, body").animate({ scrollTop: $(document).height() }, "slow");
			});
			$(this).hide();
			$("#edit_writings_hide, #edit_writings_cancel").show();
		})
		$("#edit_writings_hide").on("click", function() {
			$(".edit_writings_form").slideUp();
			$(this).hide();
			$("#edit_writings_show").show();
		})
		$("#menu_actions_export_label").bind("click", function() {
			event.preventDefault();
			$("#menu_actions_export").toggle();
		})
		$("#menu_actions_import_label").bind("click", function() {
			event.preventDefault();
			$(this).nextAll().toggle();
		})
	})
	.keyup(function(e) {
	  if (e.keyCode == 27) {
		  if ($(".menu_handle").hasClass("show")) {
				$(".more").addClass("hide").removeClass("show");
				$(".menu_actions").slideUp(400, function() {
					$("#menu_handle_show").hide();
					$("#menu_handle_hide").show();
				});
			}
		$("#table_writings input[type='text']").attr("type", "hidden");
		$(".edit_writings_form, .table_writings_comment_further_information").slideUp();
		$("#edit_writings_hide, #edit_writings_cancel").hide();
		$("#edit_writings_show").show();
	  }   
	});

function refresh_balance() {
	$.ajax({
		type: "POST",
		url : "index.php?content=timeline.ajax.php"		
	}).done(function (data) {
		$("#heading_timeline").html(data);
	});
	
	$.ajax({
		type: "POST",
		url : "index.php?content=balance.ajax.php"		
	}).done(function (data) {
			$('#menu_header_balance').html(data);
		}
	);
}