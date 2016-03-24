$(document).ready(function() {
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
        	$(".content_working").html(data);
        }
    });
}
