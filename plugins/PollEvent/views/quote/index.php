<?php if (!defined('APPLICATION')) exit();

if (!function_exists('WriteDiscussion')) :
    /**
     *
     *
     * @param $discussion
     * @param $sender
     * @param $session
     */
    function writeQuote($quote) {
        ?>
<li id="Discussion_<?php echo $quote->QuoteID; ?>" class="Item Alt Participated Mine Read ItemDiscussion">
    <span class="Options">
        </span>

            <div class="ItemContent Discussion">
                <div class="Title" role="heading" aria-level="3">
                    <?php
                    echo $quote->text;
                    ?>
                </div>
                <div class="Meta Meta-Discussion">
                    <span class="MItem"><?php
                        echo '~ ' . $quote->name;
                        ?>
                    </span>
                <span class="MItem MCount"><?php
                    echo date('Y-m-d H:i', StrToTime($quote->date));
                    ?>
                </span>
        </li>
    <?php
    }
endif;

$quotes = $this->data('quotes');


//echo "<div class='DataList Discussions'>";
echo $this->Form->open(['id' => 'Form_Quote']);
echo $this->Form->label('Wie?', 'name');
echo wrap($this->Form->textBox('name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true']), 'div', ['class' => 'TextBoxWrapper']);
echo $this->Form->label('Wat zei deze?', 'name');
echo $this->Form->bodyBox('text', ['placeholder' => 'Quote', 'title' => 'Quote']);

?>
<div class=\"Buttons\"  style="margin: 10px">
    <?php
    echo $this->Form->button('Save', ['class' => "Button Primary CommentButton"]);
    ?>
</div>
<?php
echo $this->Form->close();
//echo "</div>";

echo "<ul class='DataList Discussions'>";
foreach ($quotes as $quote) {
    writeQuote($quote);
}
echo "</ul>";
