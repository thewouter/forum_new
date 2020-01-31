<h1><?php echo t('Archive Discussions'); ?></h1>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Archive Discussions', 'Vanilla.Archive.Date');
            echo '<div class="info">',
            t('Vanilla.Archive.Description', 'You can choose to archive forum discussions older than a certain date. Archived discussions are effectively closed, allowing no new posts.'),
            '</div>'; ?>
        </div>
        <div class="input-wrap">
            <?php
            echo $this->Form->calendar('Vanilla.Archive.Date', ['placeholder' => t('Ex: 2009-01-01, 6 months, 1 year')]);
            ?>
        </div>
    </li>
</ul>
<?php echo $this->Form->close('Save'); ?>
