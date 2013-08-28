function getObj(id) {
	if (document.getElementById) {
		return document.getElementById(id);
	}
	if (document.all) {
		return document.all[id];
	}
	return null;
}

function isFunction(a) {
	return typeof a == 'function';
}
 
 function isObject(a) {
	return (typeof a == 'object' && !!a) || isFunction(a);
}

function isUndefined(a) {
	return typeof a == 'undefined';
}

function hide_obj(id) {
	if (isObject(id)) {
		var obj = id;
	} else {
		var obj = getObj(id);
	}

	obj.style.visibility = "hidden";
	obj.style.display = "none";
}

function show_obj(id, display) {
	if (show_obj.arguments.length == 1) {
		display = "inline";
	}

	if (isObject(id)) {
		var obj = id;
	} else {
		var obj = getObj(id);
	}

	obj.style.visibility = "visible";
	try {
		obj.style.display = display;
	} catch(e) {
		obj.style.display = "block";
	}
}

function state_obj(id) {
	var obj = getObj(id);
	if ("block" == obj.style.display || "visible" == obj.style.visibility || "visible" == obj.className) {
		return true;
	} else {
		return false;
	}
}

$(document).ready(function() {
	if($(".form_writing #id").attr("value") > 0) {
		$("html, body").animate({ scrollTop: $(document).height() }, "slow");
		$(".form_writing").show();
		$("#hideform").show();
		$("#showform").hide();
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
		$("#showform").show();
		$(".further_information").slideUp();
  }   
});