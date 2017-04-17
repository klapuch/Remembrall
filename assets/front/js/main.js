$('#per_page').change(function(event) {
	window.location = event.target.value;
});

$('form#delete input').last().bind('click', function() {
	return confirm('Are you sure, you want to proceed the action?');
});
