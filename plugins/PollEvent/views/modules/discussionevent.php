<?php if (!defined('APPLICATION')) exit();


if (!function_exists('displayEventDateTime')) {
    function displayEventDateTime($EventDate) {
        if ($EventDate) {
            echo '<div class="DiscussionEventDate icon icon-calendar"> ' . date_format(new dateTime($EventDate), "D j M \'y G:i") . '</div>';
        }
    }
}

if (!function_exists('displayEventDate')) {
    function displayEventDate($EventDate) {
        if ($EventDate) {
            echo '<div class="DiscussionEventDate icon icon-calendar"> ' . date_format(new dateTime($EventDate), "D j M \'y") . '</div>';
        }
    }
}

if (!function_exists('WriteDiscussionEvent')) {
	function WriteDiscussionEvent($Discussion, $Prefix) {
	?>
	<li class="<?php echo CssClass($Discussion); ?>">
		<div class="Title">
		<?php echo Anchor(Gdn_Format::Text($Discussion->Name, false), DiscussionUrl($Discussion).($Discussion->CountCommentWatch > 0 ? '#Item_'.$Discussion->CountCommentWatch : ''), 'DiscussionLink'); ?>
        </div>
        <?php displayEventDateTime($Discussion->DiscussionEventDate);?>
	</li>
	<?php
	}
}
?>

<div class="Box BoxDiscussionEvents">
	<?php echo wrap(t('Upcoming Events'), 'h2'); ?>
	<ul class="PanelInfo PanelDiscussionEvents DataList">
		<?php
		foreach ($this->Data('DiscussionEvents')->Result() as $Discussion) {
			WriteDiscussionEvent($Discussion,'');
		}
		?>
	</ul>
</div>
