$(document).ready(function() {
	if($(".edit_writings_form #id").attr("value") > 0) {
		$(".edit_writings_form").show();
		$("#edit_writings_hide").show();
		$("#edit_writings_cancel").show();
		$("#edit_writings_show").hide();
		var height = $(document).height();
		document.body.scrollTop = height;
	}
	$(".menu_actions").hide();
	$(".menu_handle").on("click", function() {
		if ($(this).hasClass("hide")) {
			$(this).addClass("show");
			$(this).removeClass("hide");
			$(".menu_actions").slideDown(400, function() {
				$("#menu_handle_hide").hide();
				$("#menu_handle_show").show();
			});
		} else {
			$(this).addClass("hide");
			$(this).removeClass("show");
			$(".menu_actions").slideUp(400, function() {
				$("#menu_handle_show").hide();
				$("#menu_handle_hide").show();
			});
		}
	})
	
	$("#edit_writings_show").on("click", function() {
		$(".edit_writings_form").slideDown(1, function() {
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");
		});
		$(this).hide();
		$("#edit_writings_hide").show();
		$("#edit_writings_cancel").show();
	})
	
	$("#edit_writings_hide").on("click", function() {
		$(".edit_writings_form").slideUp();
		$(this).hide();
		$("#edit_writings_show").show();
	})
}).keyup(function(e) {
  if (e.keyCode == 27) {
	  if ($(".menu_handle").hasClass("show")) {
			$(".more").addClass("hide");
			$(".more").removeClass("show");
			$(".menu_actions").slideUp(400, function() {
				$("#menu_handle_show").hide();
				$("#menu_handle_hide").show();
			});
		}
		$(".table_accounting input[type='text']").attr("type", "hidden");
		$(".edit_writings_form").slideUp();
		$("#edit_writings_hide").hide();
		$("#edit_writings_cancel").hide();
		$("#edit_writings_show").show();
		$(".table_writings_comment_further_information").slideUp();
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
			$('#balance').html(data);
		}
	);
}