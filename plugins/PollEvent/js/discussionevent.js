jQuery(document).ready(function($) {
	$('#Form_DiscussionEventCheck').change(function() {
		$('#Form_DiscussionEventDates1').toggle($(this).is(":checked"));
	}).trigger('change');
});
