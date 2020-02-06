<?php

class PollEventPlugin extends Gdn_Plugin {

    public static $ApplicationFolder = 'plugins/PollEvent';

    /**
     * @var DiscussionModel
     */
    private $discussionModel;

    private $views = array(
        'questions',
        );

    /**
     * Configure the plugin instance.
     *
     * @param DiscussionModel $discussionModel
     */
    public function __construct(DiscussionModel $discussionModel) {
        $this->discussionModel = $discussionModel;
    }

    /**
     * Add frontend css and js to the discussion controller
     * @param VanillaController $Sender DiscussionController
     */
    public function DiscussionController_Render_Before($Sender) {
        // Add poll voting resources
        $Sender->AddJsFile('discussionpolls.js', 'plugins/PollEvent');
        $Sender->AddCSSFile('discussionpolls.css', 'plugins/PollEvent');


        $Sender->AddDefinition('DP_ShowResults', T('Show Results'));
        $Sender->AddDefinition('DP_ShowForm', T('Show Poll Form'));
        $Sender->AddDefinition('DP_ConfirmDelete', T('Are you sure you want to delete this poll?'));

        //check for any stashed messages from poll submit
        $Message = Gdn::Session()->Stash('DiscussionPollsMessage');
        if($Message) {
            //inform
            Gdn::Controller()->InformMessage($Message);
            //pass to form error
            $Sender->SetData('DiscussionPollsMessage', $Message);
        }
    }

    /**
     * Add backend css and js to the discussion controller
     * @param VanillaController $Sender PostController
     */
    public function PostController_Render_Before($Sender) {
        // Add poll creation resources
        $Sender->AddCSSFile('admin.discussionpolls.css', self::$ApplicationFolder);
        $Sender->AddJSFile('admin.discussionpolls.js', self::$ApplicationFolder);

        //get question template for jquery poll expansion
        $DefaultQuestionString = $this->_RenderQuestionFields($Sender->Form, FALSE);
        $Sender->AddDefinition('DP_EmptyQuestion', $DefaultQuestionString);

        // Translated definitions
        $Sender->AddDefinition('DP_NextQuestion', T('Next Question'));
        $Sender->AddDefinition('DP_PrevQuestion', T('Previous Question'));
    }

    /**
     * Add Event data to new discussion form
     * @param $Sender
     * @throws Exception
     */
    public function PostController_beforeBodyInput_handler($Sender) {
        $Sender->addJsFile('discussionevent.js', self::$ApplicationFolder);
        if (!$Sender->Form->authenticatedPostBack()) {
            if (isset($Sender->Discussion) && $Sender->Discussion->DiscussionEventDate && $Sender->Discussion->DiscussionEventDuration) {
                $Sender->Form->setValue('DiscussionEventCheck', true);
                $Sender->Form->setValue('DiscussionEventDates', date_format(new DateTime($Sender->Discussion->DiscussionEventDate), 'Y-m-d\TH:i:s'));
                $Sender->Form->setValue('DiscussionEventDuration', $Sender->Discussion->DiscussionEventDuration);
            } else {
                $Sender->Form->setValue('DiscussionEventCheck', false);
                $Sender->Form->setValue('DiscussionEventDates', date('Y-m-d\T18:30:00'), new DateTime());
                $Sender->Form->setValue('DiscussionEventDuration', 3);
            }
        }

        echo '<div class="P"><div class="DiscussionEvent">';
        echo $Sender->Form->checkBox('DiscussionEventCheck', 'Is an event?');
        echo '<div class="DiscussionEventDate"><div class="P">';;
        echo $Sender->Form->hidden('DiscussionEventDates');
        echo '<div style="display: flex">';
        echo '<div style="margin-right: 10px">';
        echo $Sender->Form->label('Date', 'DiscussionEventDates', array('style' => 'width: 50%')), ' ';
//        echo '</div>';
        echo $Sender->Form->textBox('DiscussionEventDates', array(
            'type' => 'datetime-local',
            'style' => 'width: 80%;',
        ));
        echo '</div><div>';
        echo $Sender->Form->label('Hours', 'DiscussionEventDuration'), ' ';
        echo $Sender->Form->textBox('DiscussionEventDuration', array(
            'type' => 'number',
            'style' => 'width: 80%',
        ));
        echo '</div>';
        echo '</div></div></div></div>';
    }



    /**
     * Render the poll admin form on the add/edit discussion page
     * @param VanillaController $Sender PostController
     */
    public function PostController_DiscussionFormOptions_Handler($Sender) {
        $Session = Gdn::Session();
        // render check box

        $PollModel = new PollModel();
        $pollAvailable = $PollModel->Exists($Sender->Discussion->DiscussionID);
        self::log_er($pollAvailable);
        $Sender->EventArguments['Options'] .= '<li>' . $Sender->Form->CheckBox('DP_Attach', T('Attach Poll'), array('value' => '1', 'checked' => $pollAvailable)) . '</li>';
        $Sender->EventArguments['Options'] .= '<li>' . $Sender->Form->CheckBox('MultipleCheck', "Only one vote", array('value' => '1', 'checked' => FALSE)) . '</li>';
        // Load up existing poll data
        if(GetValueR('Discussion.DiscussionID', $Sender)) {
            $DID = $Sender->Discussion->DiscussionID;
        }
        else {
            $DID = NULL;
        }
        $DPModel = new PollModel();
        $DiscussionPoll = $DPModel->GetByDiscussionID($DID);

        // If there is existing poll data, disable editing
        // Editing will be in a future release

        $Disabled = array();
        $Closed = FALSE;

        $Sender->AddDefinition('DP_Closed', $Closed);

        // The opening of the form
        $Sender->Form->SetValue('DP_Title', $DiscussionPoll->Title);


        //render form
        DPRenderQuestionForm($Sender->Form, $DiscussionPoll, $Disabled, $Closed);
    }

    /**
     * Never block the ICal download page
     * @param $Sender
     */
    public function  Gdn_Dispatcher_BeforeBlockDetect_Handler($Sender) {
        $BlockExceptions =& $Sender->EventArguments['BlockExceptions'];
        $BlockExceptions['/^calendar\/ical$/'] = Gdn_Dispatcher::BLOCK_NEVER;
        self::log_er("test");
    }

    /**
     * Validate DiscussionForm
     * @param $Sender
     * @return bool
     */
    public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
        if ($Sender->EventArguments['FormPostValues']['DiscussionEventCheck']) {
            print_r($Sender->EventArguments['FormPostValues']);
            $Sender->Validation->applyRule('DiscussionEventDates', 'Required', T('Please enter an event date.'));
            $Sender->Validation->applyRule('DiscussionEventDates', 'DateTime', T('The event date you\'ve entered is invalid.'));
        } else {
            $Sender->EventArguments['FormPostValues']['DiscussionEventDate'] = null;
        }

        $FormPostValues = GetValue('FormPostValues', $Sender->EventArguments, array());
        if(!GetValue('DP_Attach', $FormPostValues)) {
            // No need to validate
            return FALSE;
        }
        if(empty($FormPostValues['DP_Attach'])) {
            return FALSE;
        }
        // Validate that all poll fields are filled out
        $Invalid = FALSE;
        $Error = '';
        if(!C('Plugins.DiscussionPolls.DisablePollTitle', FALSE) && trim($FormPostValues['DP_Title']) == FALSE) {
            $Invalid = TRUE;
            $Error = 'You must enter a valid poll title!';
        }

        // validate each question
        if(!$Invalid) {
            foreach($FormPostValues['DP_Questions'] as $QIndex => $Question) {
                if(trim($Question) == FALSE) {
                    // check to see if all the options are also blank
                    foreach($FormPostValues['DP_Options' . $QIndex] as $Option) {
                        if(trim($Option) != FALSE) {
                            $Invalid = TRUE;
                            $Error = 'You must enter valid text for question #' . ($QIndex + 1);
                        }
                    }
                    if($Invalid === FALSE) {
                        // remove the question
                        unset($Sender->EventArguments['FormPostValues']['DP_Questions'][$QIndex]);
                        // unsetting the options will prevent any more questions from being added
                        unset($Sender->EventArguments['FormPostValues']['DP_Options' . $QIndex]);
                    }
                    break;
                }
                else {
                    $OptionCount = 0;
                    foreach($FormPostValues['DP_Options' . $QIndex] as $OIndex => $Option) {
                        if(trim($Option) == FALSE) {
                            // unset that option
                            unset($Sender->EventArguments['FormPostValues']['DP_Options' . $QIndex][$OIndex]);
                        }
                        else {
                            $OptionCount++;
                        }
                    }
                    if($OptionCount < 2) {
                        $Invalid = TRUE;
                        $Error = 'You must enter at least two valid options for question #' . ($QIndex + 1);
                        break;
                    }
                }
            }
        }
        if($Invalid) {
            $Error = Wrap('Error', 'h1') . Wrap($Error, 'p');
            // should prevent the discussion from being saved
            die($Error);
        }
        return TRUE;
    }

    /**
     * Save poll when saving a discussion
     * @param VanillaModel $Sender DiscussionModel
     * @return boolean if the poll was saved
     * @throws Exception
     */
    public function DiscussionModel_AfterSaveDiscussion_Handler($Sender) {
        $FormPostValues = GetValue('FormPostValues', $Sender->EventArguments, array());
        // Don't trust the discussion ID implicitly
        $DiscussionID = GetValue('DiscussionID', $Sender->EventArguments, 0);
        if($DiscussionID == 0) {
            $Error = Wrap('Error', 'h1') . Wrap('Invalid discussion id', 'p');
            return FALSE;
        }

        $this->saveEvent($FormPostValues, $DiscussionID);
        $this->savePoll($FormPostValues, $DiscussionID);
    }

    /**
     * Save Event when saving a discussion
     * @param $FormPostValues
     * @param $DiscussionID
     * @throws Exception
     */
    private function saveEvent($FormPostValues, $DiscussionID) {
        $EventModel = new EventModel();
        if (GetValue('DiscussionEventCheck', $FormPostValues)) {
            $EventModel->SaveDiscussionEventDate($DiscussionID, new dateTime($FormPostValues['DiscussionEventDates']), $FormPostValues['DiscussionEventDuration']);
       } else {
            $EventModel->RemoveDiscussionEventDate($DiscussionID);
        }
    }

    /**
     * @param $FormPostValues
     * @param $DiscussionID
     * @return bool
     * @throws Exception
     */
    private function savePoll($FormPostValues, $DiscussionID){
        $DPModel = new PollModel();
        // Unchecking the poll option will remove the poll
        if(!GetValue('DP_Attach', $FormPostValues)) {
            // Delete existing poll
            if($DPModel->Exists($DiscussionID)) {
                Gdn::Controller()->InformMessage(T('Plugins.DiscussionPolls.PollRemoved', 'The attached poll has been removed'));
                $DPModel->DeleteByDiscussionID($DiscussionID);
                return FALSE;
            }
        } else {
            if($DPModel->Exists($DiscussionID)) {
                // Update poll when poll exists
                $DPModel->Update($FormPostValues);
                return TRUE;
            }
            // Check to see if there are already poll responses; exit
            if($DPModel->HasResponses($DiscussionID) ) {
                Gdn::Controller()->InformMessage(T('Plugins.DiscussionPolls.UnableToEditAfterResponses', 'You do not have permission to edit a poll with responses.'));
                return FALSE;
            }
            // Validate that all poll fields are filled out
            $Invalid = FALSE;
            $Error = '';
            if(trim($FormPostValues['DP_Title']) == FALSE && !C('Plugins.DiscussionPolls.DisablePollTitle', FALSE)) {
                $Invalid = TRUE;
                $Error = 'You must enter a valid poll title!';
            }
            foreach($FormPostValues['DP_Questions'] as $Index => $Question) {
                if(trim($Question) == FALSE) {
                    $Invalid = TRUE;
                    $Error = 'You must enter valid question text!';
                    break;
                }
            }

            if($Invalid) {
                // fail silently since this shouldn't happen
                $Error = Wrap('Error', 'h1') . Wrap($Error, 'p');
                return FALSE;
            }
            else {
                // save poll form fields
                $DPModel->Save($FormPostValues);
                return TRUE;
            }
        }
    }

    /**
     * Show upcoming activities
     * @param $Sender
     */
    public function DiscussionsController_AfterPageTitle_handler($Sender){
        $DiscussionEventModule = new DiscussionEventModule($Sender);
        echo $DiscussionEventModule->toString();
    }

    /**
     * Insert poll in first post of discussion in 2.1b1
     * @param VanillaController $Sender DiscussionController
     */
    public function DiscussionController_AfterDiscussionBody_Handler($Sender) {
        $this->_PollInsertion($Sender);
    }

    /**
     * Determines what part of the poll (if any) needs to be rendered
     * Checks permissions and displays any tools available to user
     * @param VanillaController $Sender
     * @return type
     */
    protected function _PollInsertion($Sender) {
        $Discussion = $Sender->Discussion;
        $Session = Gdn::Session();
        $DPModel = new PollModel();

        // Does an attached poll exist?
        if($DPModel->Exists($Discussion->DiscussionID)) {
            $Results = FALSE;
            $Closed = FALSE;
            $Poll = $DPModel->GetByDiscussionID($Discussion->DiscussionID);
            // Can the current user view polls?
            if(!$Session->CheckPermission('Plugins.DiscussionPolls.View')) {
                // make this configurable?
                echo Wrap(T('Plugins.DiscussionPolls.NoView', 'You do not have permission to view polls.'), 'div', array('class' => 'DP_AnswerForm'));
                return;
            }
            // Check to see if the discussion is closed
            if($Discussion->Closed) {
                // Close the Poll if the discussion is closed (workaround)
                $DPModel->Close($Discussion->DiscussionID);
                $Closed = TRUE;
            }

            // Has the user voted?
            if($DPModel->HasAnswered($Poll->PollID, $Session->UserID) || !$Session->IsValid() || $Closed) {
                $Results = TRUE;

                // Render results
                $ResultsModule = new ResultsModule();
                $ResultsModule->setData('Poll', $Poll);
                echo  $ResultsModule->toString();

            } else {

                $PartialAnswers = $DPModel->PartialAnswer($Poll->PollID, $Session->UserID);
                //if some saved partial answers inform
                if(!empty($PartialAnswers)) {
                    // TODO: Remove?
                    Gdn::Controller()->InformMessage(T('Plugins.DiscussionPolls.LoadedPartial', 'Your answered questions have been loaded.'));
                }
                // Render the submission form
                $this->_RenderVotingForm($Sender, $Poll, $PartialAnswers, true, false);
            }

            // Render poll tools
            // Owner and Plugins.DiscussionPolls.Manage gets delete if exists and attach if it doesn't
            // Plugins.DiscussionPolls.View gets show results if the results aren't shown
            $Tools = '';
            if($Discussion->InsertUserID == $Session->UserID || $Session->CheckPermission('Plugins.DiscussionPolls.Manage')) {
                $Tools .= Wrap(
                    Anchor(T('Delete Poll'), '/poll/delete/' . $Poll->PollID), 'li', array('id' => 'DP_Remove')
                );
            }

            $Tools .= Wrap(
               Anchor(T('Show Results'), '/poll/results/' . $Poll->PollID), 'li', array(
                   'id' => 'DP_Results',
                   'style' => !$Results ? "" : "display:none;",
                   )
            );

            $Tools .= Wrap(
                Anchor(T('Remove Vote'), '/poll/devote/' . $Poll->PollID), 'li', array(
                    'id' => 'DP_Devote',
                    'style' => "display: none;",
                    )
            );


            echo WrapIf($Tools, 'ul', array('id' => 'DP_Tools'));
        }
        else {
            // Poll does not exist
            if($Discussion->InsertUserID == $Session->UserID || $Session->CheckPermission('Plugins.DiscussionPolls.Manage')) {
                echo Wrap(
                    Wrap(
                        Anchor(T('Attach Poll'), '/vanilla/post/editdiscussion/' . $Discussion->DiscussionID), 'li'), 'ul', array('id' => 'DP_Tools')
                );
            }
        }
    }

    /**
     * Show date on discussions pages.
     *
     * @param DiscussionController $sender
     * @param array $args
     */
    public function discussionsController_afterDiscussionTitle_handler($Sender, array $args) {
       if ($args['Discussion'] != ''){
           echo "<br>";
           displayEventDate($args['Discussion']->DiscussionEventDate);
       }
    }

    /**
     * Renders a poll object as results
     * @param stdClass $Poll the poll object we are rendering
     * @param boolean $Echo echo or return result string
     * @return mixed Will return string if $Echo is false, will return true otherwise
     */
    public function _RenderResults($Poll, $Echo = TRUE) {
    $ResultsModule = new ResultsModule();
    $ResultsModule->setData('Poll', $Poll);
        if($Echo) {
            echo $ResultsModule->toString();
            return TRUE;
        }
        else {
            ob_start();
            echo $ResultsModule->toString();
            $Result = ob_get_contents();
            ob_end_clean();
            return $Result;
        }
    }

    /**
     * Set view that can be copied over to current theme
     * e.g. view -> current_theme/views/plugins/DiscussionPolls/view.php
     * @param View name of the view
     * @return string
     */
    public function ThemeView($View) {
        $ThemeViewLoc = CombinePaths(array(
            PATH_THEMES, Gdn::Controller()->Theme, 'views', $this->GetPluginFolder(FALSE)
        ));

        if(file_exists($ThemeViewLoc . DS . $View . '.php')) {
            $View = $ThemeViewLoc . DS . $View . '.php';
        }
        else {
            $View = $this->GetView($View . '.php');
        }

        return $View;
    }

    public function Base_Render_before($Sender) {

        echo "<style> footer{display: none;} </style>";


        // only add the module if we are in the panel asset and NOT in the dashboard
        if (getValue('Panel', $Sender->Assets) && $Sender->MasterView != 'admin') {
            $DiscussionEventModule = new DiscussionEventModule($Sender);
            $Sender->addModule($DiscussionEventModule);
        }

    }

    /**
     * Renders / fetches question fields for form
     * @param stdClass $PollForm the poll object we are rendering
     * @param boolean $Echo echo or return result string
     * @return mixed Will return string if $Echo is false, will return true otherwise
     */
    protected function _RenderQuestionFields($PollForm, $Echo = TRUE) {
        include_once($this->ThemeView('questions'));

        if($Echo) {
            DPRenderQuestionField($PollForm);
            return TRUE;
        } else {
            ob_start();
            DPRenderQuestionField($PollForm);
            $Result = ob_get_contents();
            ob_end_clean();
            return $Result;
        }
    }

    /**
     * Renders a voting form for a poll object
     * @param VanillaController $Sender controller object
     * @param stdClass $Poll poll object
     * @param boolean $Echo echo or return result string
     * @return mixed Will return string if $Echo is false, will return true otherwise
     */
    protected function _RenderVotingForm($Sender, $Poll, $PartialAnswers, $Echo = TRUE, $Show = TRUE) {
        $Sender->PollForm = new Gdn_Form();
        $Sender->PollForm->AddHidden('DiscussionID', $Poll->DiscussionID);
        $Sender->PollForm->AddHidden('PollID', $Poll->PollID);

        if($Sender->Data('DiscussionPollsMessage')) {
            $Sender->PollForm->AddError($Sender->Data('DiscussionPollsMessage'));
        }

//        include_once($this->ThemeView('voting'));
        $VotingModule = new VotingModule();
        $VotingModule->setData('PollForm', $Sender->PollForm);
        $VotingModule->setData('Poll', $Poll);
        $VotingModule->setData('PartialAnswers', $PartialAnswers);
        $VotingModule->setData('Show', $Show);

        if($Echo) {
            $str = $VotingModule->toString();
            echo $str; //DiscussionPollAnswerForm($Sender->PollForm, $Poll, $PartialAnswers);
            return TRUE;
        }
        else {
            ob_start();
            echo $VotingModule->toString(); //DiscussionPollAnswerForm($Sender->PollForm, $Poll, $PartialAnswers);
            $Result = ob_get_contents();
            ob_end_clean();
            return $Result;
        }
    }

    /**
     * Remove attached poll when discussion is deleted
     * @param VanillaModel $Sender DiscussionModel
     */
    public function DiscussionModel_DeleteDiscussion_Handler($Sender) {
        // Get discussionID that is being deleted
        $DiscussionID = $Sender->EventArguments['DiscussionID'];

        // Delete via model
        $DPModel = new PollModel();
        $DPModel->DeleteByDiscussionID($DiscussionID);
    }

    /**
     * Log to php error log
     * @param $msg
     */
    public static function log_er($msg){
        file_put_contents('php://stderr', print_r($msg, TRUE));
        file_put_contents('php://stderr', print_r("\n", TRUE));
    }

    /**
     * Run once on enable.
     *
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Setup database structure for model
     */
    public function structure() {
        $Database = Gdn::Database();
        $Construct = $Database->Structure();

        $Construct->table('Discussion')
            ->column('DiscussionEventDate', 'datetime', true)
            ->column('DiscussionEventDuration', 'int', true)
            ->Set();

        $Construct->Table('DiscussionPolls');
        $Construct
            ->PrimaryKey('PollID')
            ->Column('DiscussionID', 'int', FALSE, 'key')
            ->Column('Text', 'varchar(140)', TRUE)
            ->Column('Open', 'tinyint(1)', '1')
            ->Set();

        $Construct->Table('DiscussionPollQuestions');
        $Construct
            ->PrimaryKey('QuestionID')
            ->Column('PollID', 'int', FALSE, 'key')
            ->Column('Text', 'varchar(140)')
            ->Column('CountResponses', 'int', '0')
            ->Column('MultipleVote', 'int', '0')
            ->Set();

        $Construct->Table('DiscussionPollQuestionOptions');
        $Construct
            ->PrimaryKey('OptionID')
            ->Column('QuestionID', 'int', FALSE, 'key')
            ->Column('PollID', 'int', FALSE, 'key')
            ->Column('Text', 'varchar(140)')
            ->Column('CountVotes', 'int', '0')
            ->Column('Voters', 'varchar(140)', '')
            ->Set();

        $Construct->Table('DiscussionPollAnswers');
        $Construct
            ->PrimaryKey('AnswerID')
            ->Column('PollID', 'int', FALSE, 'key')
            ->Column('QuestionID', 'int', FALSE, 'key')
            ->Column('UserID', 'int', FALSE, 'key')
            ->Column('OptionID', 'int', TRUE, 'key')
            ->Set();

        $Construct->Table('DiscussionPollAnswerPartial');
        $Construct
            ->Column('PollID', 'int', FALSE, 'index.1') // multicolumn for quick lookup
            ->Column('QuestionID', 'int', FALSE)
            ->Column('UserID', 'int', FALSE, 'index.1')
            ->Column('OptionID', 'int', FALSE)
            ->Set();
    }
}
