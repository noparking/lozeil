$(document).ready(function() {
    
	$(".modif")
	.on("click", function() {
	    var id = $( this ).attr("id");
	    var donnee = {action:'form_modif', id: id };
	    $.post(
		"index.php?content=account.ajax.php",
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
	     if ($(this).val() != "none") {
		 if (confirm("êtes vous sûr ?")) {
		     $('#form_params').submit();
		 }
	     }
	 }	    
	}
    );
});