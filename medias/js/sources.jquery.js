$(document).ready(function() {
   
	$("body")
	.on("click", "#checkbox_all_up, #checkbox_all_down", function() {
			var $checkboxes = $("#table_sources, #select_sources").find(':checkbox');
			if (this.checked) {
				$checkboxes.each(function() {
					$(this)[0].checked = true
				});
			} else {
				$checkboxes.each(function() {
					$(this)[0].checked = false
				});
			}
		})

	$(".modif")
	.on("click", function() {
	    var id = $( this ).attr("id");
	    var donnee = {action:'form_modif', id: id };
	    $.post(
		"index.php?content=sources.ajax.php",
		donnee,
		function(data){
		    var result = jQuery.parseJSON(data);
		    if (result.status == "true") {
			console.log(result.data);
			$.colorbox({html: result.data});
		    }
		}
	    );
	    return true;
	});

	$(".add")
	.on("click", function() {
	    var donnee = {action:'form_add'};
	    $.post(
		"index.php?content=sources.ajax.php",
		donnee,
		function(data){
		    var result = jQuery.parseJSON(data);
		    if (result.status == "true") {
			console.log(result.data);
			$.colorbox({html: result.data});
		    }
		}
	    );
	    return true;
	});

    $('#action').on(
	{change:
	 function () {
	     if ($(this).val()=="add") {
		 $.post(
		     "index.php?content=sources.ajax.php",
		     {action:"form_add"},
		     function(data) {
			 var result = jQuery.parseJSON(data);
			 $.colorbox({html:result.data});
		     }
		 );
	     }
	     else if ($(this).val() != "none") {

		 $("#form_sources").submit();
	     }
	 }	    
	}
    );
});