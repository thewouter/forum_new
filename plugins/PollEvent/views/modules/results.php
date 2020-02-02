<?php if(!defined('APPLICATION')) exit();
/* 	Copyright 2013-2014 Zachary Doll */
function DPRenderResults($Poll) {
    $TitleExists = GetValue('Title', $Poll, FALSE);
    $HideTitle = C('Plugins.DiscussionPolls.DisablePollTitle', FALSE);
    echo '<div class="DP_ResultsForm">';

    if($TitleExists || $HideTitle) {
        $TitleS = $Poll->Title;
        if(trim($Poll->Title) != FALSE) {
            $TitleS .= '<hr />';
        }
    }
    else {
        $TitleS = Wrap(T('Plugins.DiscussionPolls.NotFound', 'Poll not found'));
    }
    echo $TitleS;

    echo '<ol class="DP_ResultQuestions">';
    if(!$TitleExists && !$HideTitle) {
        //do nothing
    }
    else if(!count($Poll->Questions)) {
        echo Wrap(T('Plugins.DiscussionPolls.NoResults', 'No results for this poll'));
    }
    else {
        echo Wrap("", 'input', array('id' => 'Form_PollID', 'type' => 'hidden', 'value' => $Poll->PollID));
        foreach($Poll->Questions as $Question) {
            RenderQuestion($Question);
        }
    }
    echo '</ol>';
    echo '</div>';
}

function RenderQuestion($Question) {
    echo '<li class="DP_ResultQuestion">';
    echo Wrap($Question->Title, 'span');
    echo Wrap(sprintf(Plural($Question->CountResponses, '%s vote', '%s votes'), $Question->CountResponses), 'span', array('class' => 'Number DP_VoteCount'));
    // 'randomize' option bar colors
    $k = $Question->QuestionID % 10;
    echo '<ol class="DP_ResultOptions">';
    foreach($Question->Options as $Option) {
        $String = Wrap($Option->Title . ($Option->Voters != "" ? '  (' . count(explode(',',$Option->Voters)) . ' Wortel(s)).':""), 'div');
        $Percentage = ($Question->CountResponses == 0) ? '0.00' : number_format(($Option->CountVotes / $Question->CountResponses * 100), 2);
        // Put text where it makes sense
        if($Percentage < 10) {
            $String .= '<span class="DP_Bar DP_Bar-' . $k . '" style="width: ' . $Percentage . '%;">&nbsp</span> ' . $Percentage . '% ' . $Option->Voters;
        }
        else {
            $String .= '<span class="DP_Bar DP_Bar-' . $k . '" style="width: ' . $Percentage . '%;">' . $Percentage . '% ' . $Option->Voters . '</span>';
        }
        echo Wrap($String, 'li', array('class' => 'DP_ResultOption'));
        $k = ++$k % 10;
    }
    echo '</ol>';
    echo '</li>';
}

DPRenderResults($this->data('Poll'));
