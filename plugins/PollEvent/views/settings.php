<?php if(!defined("APPLICATION")) exit();

echo Wrap(T($this->Data['Title']), 'h1');

echo $this->Form->Open();
echo $this->Form->Errors();

echo Wrap(
        Wrap(
                $this->Form->Label(T('Show Results'), 'Plugins.DiscussionPolls.EnableShowResults') .
                Wrap($this->Form->CheckBox('Plugins.DiscussionPolls.EnableShowResults') .
                        T('Allow users to view results without voting'), 'div', array('class' => 'Info'), 'li') .
                $this->Form->Label(T('Poll Title'), 'Plugins.DiscussionPolls.DisablePollTitle') .
                Wrap($this->Form->CheckBox('Plugins.DiscussionPolls.DisablePollTitle') .
                        T('Disable poll titles'), 'div', array('class' => 'Info')), 'li'), 'ul');

echo $this->Form->Close("Save");
?>
<div class="Footer">
<?php
echo Wrap(T('Feedback'), 'h3');
?>
	<li>
	<?php echo $this->Form->Label(T('Display in Side Panel'), 'Plugins.DiscussionEvent.DisplayInSidepanel'); ?>
	<?php echo $this->Form->CheckBox('Plugins.DiscussionEvent.DisplayInSidepanel', T('Show upcoming events in side panel?')); ?>
	<p><small><strong>Note:</strong> To manually insert the list of upcoming events into your site, paste e.g. <i>{module name="DiscussionEventModule" CategoryID=6 Limit=3}</i> into your theme's <i>view.master.tpl</i> file to show three upcoming events from the sixth category.</b></small></p>
	</li>
	<li>
	<?php echo $this->Form->Label(T('Number of Displayed Events'), 'Plugins.DiscussionEvent.MaxDiscussionEvents'); ?>
	<p>The maximum number of upcoming events to be shown.</p>
	<?php echo $this->Form->TextBox('Plugins.DiscussionEvent.MaxDiscussionEvents', array('placeholder' => '10')); ?>
	</li>
</div>
<?php echo $this->Form->Close('Save'); ?>

<div class="Info">
	<p>Do you have questions or feedback? Please email me at <a href="mailto:wouter@radixenschede.nl">wouter@radixenschede.nl</a> or build a pull request at <a href="https://github.com/thewouter/radixforumvanilla"> the git repository</a>.</p>
	</div>
