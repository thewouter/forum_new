jQuery(document).ready(function($) {
	$('#Form_DiscussionEventCheck').change(function() {
		$('.DiscussionEventDate').toggle($(this).is(":checked"));
	}).trigger('change');
});
