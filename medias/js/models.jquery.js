$(document).ready(function() {

	$(".content_working")
	.on('click', 'input[type=\'submit\']:not(#select)', function() {
		var data = $(this).data('active');
		$("#model").find("div").each(function() {
			$(this).hide();
		})
		$("#model").find("#"+data).show();
	})

	$("#model")
	.on('click', 'input[id=\'select\']', function() {
		$(this).prev().prev().select();
	})
});