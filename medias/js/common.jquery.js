$(document).ready(function() {
	if($(".form_writing #id").attr("value") > 0) {
		$(".form_writing").show();
		$("#hideform").show();
		$("#newform").show();
		$("#showform").hide();
		var height = $(document).height();
		document.body.scrollTop = height;
	}
	$(".actions").hide();
	$(".more").on("click", function() {
		if ($(this).hasClass("hide")) {
			$(this).addClass("show");
			$(this).removeClass("hide");
			$(".actions").slideDown(400, function() {
				$("#menu_more").hide();
				$("#menu_less").show();
			});
		} else {
			$(this).addClass("hide");
			$(this).removeClass("show");
			$(".actions").slideUp(400, function() {
				$("#menu_less").hide();
				$("#menu_more").show();
			});
		}
	})
	
	$("#showform").on("click", function() {
		$(".form_writing").slideDown(1, function() {
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");
		});
		$(this).hide();
		$("#hideform").show();
		$("#newform").show();
	})
	
	$("#hideform").on("click", function() {
		$(".form_writing").slideUp();
		$(this).hide();
		$("#showform").show();
	})
}).keyup(function(e) {
  if (e.keyCode == 27) {
	  if ($(".more").hasClass("show")) {
			$(".more").addClass("hide");
			$(".more").removeClass("show");
			$(".actions").slideUp(400, function() {
				$("#menu_less").hide();
				$("#menu_more").show();
			});
		}
		$(".table_accounting input[type='text']").attr("type", "hidden");
		$(".form_writing").slideUp();
		$("#hideform").hide();
		$("#newform").hide();
		$("#showform").show();
		$(".further_information").slideUp();
  }   
});

function refresh_balance() {
	$.ajax({
		type: "POST",
		url : "index.php?content=timeline.ajax.php"		
	}).done(function (data) {
		$(".timeline").remove();
		$(".heading").prepend(data);
	});
	
	$.post(
	"index.php?content=balance.ajax.php",
		function(data) {
			$('.balance_summary').html(data);
		}
	);
}