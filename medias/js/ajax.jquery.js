$(document).ready(function() {
	$("body").on("mouseenter", "td.operations", function() {
		$(this).find("a:not(.edit)").css("display", "inline-block");
	});
	
	$("body").on("mouseleave", "td.operations", function() {
		$(this).find("a:not(.edit)").hide();
	});

	$(".content").on("click", ".ajax", function() {
	    var url = $(this).attr("href");
	    $.post(url, {theme:"ajax"}, function(data) {
	    	$.colorbox({html: data});
	    	$(".content-ajax form").ajaxForm({
	    		url: url,
	    		error: close_colorbox_and_refresh,
	    		success: close_colorbox_and_refresh
	    	});
	    });
	    return false;
	});
});

function close_colorbox_and_refresh() {
	$.colorbox.close();
	$.ajax({
		url: document.location,
        data: {theme: "ajax"},
        type: "GET",
        success: function(data){
        	var content = $(data).html();
        	$(".content_working").html(content)
        		.promise().done(function() {refresh_balance();});
        }
    });
}
