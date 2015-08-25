$(document).ready(function() {

	$('body')
	.on("click", ".name", function() {
		var id = $(this).parent().attr("id");
		$("#accounting_codes").find("." + id).toggle();
		if ($(this).parent().next().css("display") == "none") {
			$("#accounting_codes").find("tr[class^=" + id + "]").hide();
		}
	})
	
	$('.modify_accountingcode').on(
	{ click:
		function (e) {
			var donnee = { accountingcodes_id: $(this).parent().parent().attr("id"), action:'form_edit'};
			$.post(
				"index.php?content=accountingplan.ajax.php",
				donnee,
				function(data) {
					var result = jQuery.parseJSON(data);
					if (result.status == "true") {
						$.colorbox({html:result.data});
					}
		 		}
			);
		}
	});

	$('.add_accountingcode').on(
	{ click:
		function (e) {
			var donnee = { accountingcodes_id: $(this).parent().parent().attr("id"), action:'form_add'};
			$.post(
				"index.php?content=accountingplan.ajax.php",
				donnee,
				function(data) {
					var result = jQuery.parseJSON(data);
					if (result.status == "true") {
						$.colorbox({html:result.data});
					}
				}
			);
		}
	});
	
	$('.delete_accountingplan').on(
	{ click:
		function () {
			var conf = confirm("Êtes-vous sûr?");
			if (conf) {
				var id = $(this).parent().parent().attr('id');
				var donnee = { accountingcodes_id: id , action:'delete'};
				$.post(
					"index.php?content=accountingplan.ajax.php",
					donnee,
					function(data) {
						var result = jQuery.parseJSON(data);
						if (result.status == "true") {
							location.reload();
							show_status(result.data);
						}
					}
				);
			}
		}
	});

	$('#import_default').on(
	{ click:
		function () {
			var confirmation = confirm(message_confirm);
			if (confirmation) {
				var donnee = { accountingcodes_id: 0, action: 'import_default'};
				$.post(
					"index.php?content=accountingplan.ajax.php",
					donnee,
					function(data) {
						var result = jQuery.parseJSON(data);
						if (result.status == "true") {
							$(location).attr('href',$(location).attr('href'));
							location.reload();
						}
					}
			 	);
			}
		}
	});
})

function add_accountingcode() {
	var name = $("#accountingcode_new_name").val();
	var number = $("#accountingcode_new_number").val() ;
	var donnee = { accountingcodes_id: number , accountingcodes_name: name , action:'add'};
	$.post(
		"index.php?content=accountingplan.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			if (result.status == "true") {
				$(location).attr('href',$(location).attr('href'));
			}
			$.colorbox.close();
			show_status(result.data);
		}
	);
}

function modify_accountingcode() {
	var id = $("#accountingcode_id").val();
	var name = $("#accountingcode_name").val() ;
	var donnee = { accountingcodes_id: id , action:'edit' , accountingcodes_name: name};
	$.post(
		"index.php?content=accountingplan.ajax.php",
		donnee,
		function(data) {
			var result = jQuery.parseJSON(data);
			if (result.status == "true") {
				$('#'+id + " .accounting_codes_names").text(name);
			}
			$.colorbox.close();
			location.reload();
		}
	);
}