<?php

class PollModel extends Gdn_Model{

    //query cache
    private static $Cache = array('Exists' => array(), 'Answered' => array(), 'Responses' => array(), 'Partial' => array(), 'Get' => array());

    /**
     * Class constructor. Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('DiscussionPolls');
    }

    /**
     * Determines if a poll associated with the discussion exists
     * @param int $DiscussionID
     * @return boolean
     */
    public function Exists($DiscussionID) {
        //check for cached result
        $Data = GetValueR('Exists.' . $DiscussionID, self::$Cache);

        if(!empty($Data)) {
            return TRUE;
        }

        $this->SQL
            ->Select('PollID')
            ->From('DiscussionPolls')
            ->Where('DiscussionID', $DiscussionID);

        $Data = $this->SQL->Get()->FirstRow();
        //store in cache
        self::$Cache['Exists'][$DiscussionID] = $Data;
        return !empty($Data);
    }

    /**
     * Updates the data associated with the poll
     * @param mixed $FormPostValues Array with values of the Form
     * @return boolean true if succes
     */
    public function Update($FormPostValues, $Where = false, $Limit = false){

        // Get PollID of existing poll
        $PollID = $this->GetIDByDiscussionID($FormPostValues['DiscussionID']);
        // Get QuistionID of existing questions
        $this->SQL
            ->Select('q.QuestionID')
            ->From('DiscussionPollQuestions q')
            ->Where('q.PollID', $PollID);
        $QuestionIDs = $this->SQL->Get()->result();


        // Update Text
        $this->SQL
            ->Update('DiscussionPolls')
            ->Set('Text', $FormPostValues['DP_Title'])
            ->Where('DiscussionID', $FormPostValues['DiscussionID'])
            ->Put();
        // Update existing Questions
        $oldIDs = array();
        $Titles = $FormPostValues['DP_Questions'];
        for ($i=0; $i < count($QuestionIDs); $i++) {
            $QuestionID = $FormPostValues['QuestionID'.$i];
            array_push($oldIDs,intval($QuestionID));
            // Update title
            $this->SQL
                ->Update('DiscussionPollQuestions')
                ->Set('Text', $Titles[$i])
                ->Set('MultipleVote', 1-$FormPostValues['MultipleCheck'])
                ->Where('QuestionID',$QuestionID)
                ->Put();
            // Update Options
            $this->SQL
                ->Select('o.OptionID')
                ->From('DiscussionPollQuestionOptions o')
                ->Where('o.QuestionID', $QuestionID);
            $OptionIDs = $this->SQL->Get()->result();
            $OptionTexts = $FormPostValues['DP_Options'.$i];
            foreach ($OptionIDs as $ii => $option) {
                $option = $option->OptionID;
                if(empty($OptionTexts[$ii])){
                    $this->SQL
                        ->Update('DiscussionPollQuestionOptions')
                        ->Set('Text', ">Verwijderd")
                        ->Where('OptionID',$option)
                        ->Put();
                } else {
                    $this->SQL
                        ->Update('DiscussionPollQuestionOptions')
                        ->Set('Text', $OptionTexts[$ii])
                        ->Where('OptionID',$option)
                        ->Put();
                }
            }
            if(count($OptionTexts) > count($OptionIDs)){
                for($ii = count($OptionIDs); $ii < count($OptionTexts); $ii++){
                    $this->SQL
                        ->Insert('DiscussionPollQuestionOptions', array(
                                'QuestionID' => $QuestionID,
                                'PollID' => $PollID,
                                'Text' => Gdn_Format::Text($OptionTexts[$ii]))
                        );
                }
            }
        }
        $shift = count($QuestionIDs);
        if (count($QuestionIDs)<count($Titles)) { // Insert new questions
            for ($i=0; $i < count($QuestionIDs); $i++) {
                array_shift($FormPostValues['DP_Questions']);
            }
            try {
                $this->Database->BeginTransaction();

                // Insert the questions
                foreach($FormPostValues['DP_Questions'] as $Index => $Question) {
                    $this->SQL
                        ->Insert('DiscussionPollQuestions', array(
                                'PollID' => $PollID,
                                'Text' => Gdn_Format::Text($Question),
                                'MultipleVote' => 1-$FormPostValues['MultipleCheck'])
                        );
                }

                // Select the question IDs
                $this->SQL
                    ->Select('q.QuestionID')
                    ->From('DiscussionPollQuestions q')
                    ->Where('q.PollID', $PollID);
                $QuestionIDs = $this->SQL->Get()->Result();

                // Insert the Options
                foreach($QuestionIDs as $Index => $QuestionID) {
                    if(in_array(intval($QuestionID->QuestionID), $oldIDs)){
                        continue;
                    }
                    $QuestionOptions = ArrayValue('DP_Options' . ($Index), $FormPostValues);
                    foreach($QuestionOptions as $Option) {
                        $this->SQL
                            ->Insert('DiscussionPollQuestionOptions', array(
                                    'QuestionID' => $QuestionID->QuestionID,
                                    'PollID' => $PollID,
                                    'Text' => Gdn_Format::Text($Option))
                            );
                    }
                }

                $this->Database->CommitTransaction();
            }
            catch(Exception $Ex) {
                $this->Database->RollbackTransaction();
                throw $Ex;
            }
        }
    }

    /**
     * removes the vote from the poll
     * @param int $UserID
     * @param int $PollID
     * @return boolean succes
     */
    public function DeVote($UserID, $PollID){
        if($UserID > 0 && $PollID > 0){
            try {
                $this->SQL
                    ->select('a.OptionID')
                    ->from('DiscussionPollAnswers a')
                    ->where(array('PollID' => $PollID,  'UserID' => $UserID));
                $OptionIDs = $this->SQL->Get()->Result();
                $questions = array();
                foreach ($OptionIDs as $O){
                    $OptionID = array($O);
                    $this->SQL
                        ->select('a.QuestionID')
                        ->from('DiscussionPollQuestionOptions a')
                        ->where(array('OptionID' => $OptionID['0']->OptionID));
                    $QuestionID = $this->SQL->Get()->Result();
                    $this->SQL
                        ->Update('DiscussionPollQuestionOptions')
                        ->Set('CountVotes', 'CountVotes - 1', FALSE)
                        ->Where('OptionID', $OptionID['0']->OptionID)
                        ->Put();

                    if(!in_array($QuestionID['0']->QuestionID, $questions)){
                        array_push($questions, $QuestionID['0']->QuestionID);
                        $this->SQL
                            ->Update('DiscussionPollQuestions')
                            ->Set('CountResponses', 'CountResponses - 1', FALSE)
                            ->Where('QuestionID', $QuestionID['0']->QuestionID)
                            ->Put();
                    }
                    $this->SQL->Delete('DiscussionPollAnswers', array('PollID' => $PollID, 'UserID' => $UserID));
                }
            } catch (Exception $e) {
                throw $e;
            }
        }
        return true;
    }


    /**
     * Determines if a poll associated with the discussion has been answered at all
     * @param int $DiscussionID
     * @return boolean
     */
    public function HasResponses($DiscussionID) {
        //check for cached result
        $Data = GetValueR('Responses.' . $DiscussionID, self::$Cache);

        if(!empty($Data)) {
            return $Data;
        }

        $this->SQL
            ->Select('p.PollID')
            ->From('DiscussionPolls p')
            ->Join('DiscussionPollAnswers a', 'p.PollID = a.PollID')
            ->Where('p.DiscussionID', $DiscussionID);

        $Data = $this->SQL->Get()->Result();
        //store in cache
        self::$Cache['Responses'][$DiscussionID] = $Data;
        return !empty($Data);
    }

    /**
     * Gets a poll object associated with a poll ID which does not include votes
     * @param int $PollID
     * @return stdClass Poll object
     */
    public function Get($PollID = '', $OrderDirection = 'asc', $Limit = false, $PageNumber = false) {
        //check for cached result
        $Data = GetValueR('Get.' . $PollID, self::$Cache);

        if(!empty($Data)) {
            return $Data;
        }

        $this->SQL
            ->Select('p.*')
            ->Select('q.Text', '', 'Question')
            ->Select('q.QuestionID')
            ->Select('q.CountResponses')
            ->Select('q.MultipleVote')
            ->Select('o.Text', '', 'Option')
            ->Select('o.CountVotes', '', 'CountVotes')
            ->Select('o.Voters', '', 'Voters')
            ->Select('o.OptionID', '','OptionID')
            ->Select('o.OptionID', '','OptionIDs')
            ->Select('a.UserID')
            ->Select('a.OptionID')
            ->Select('u.UserID')
            ->Select('u.Name', '', 'Name')
            ->From('DiscussionPolls p')
            ->Join('DiscussionPollQuestions q', 'p.PollID = q.PollID')
            ->Join('DiscussionPollQuestionOptions o', 'q.QuestionID = o.QuestionID')
            ->Join('DiscussionPollAnswers a', 'a.OptionID = o.OptionID', 'left outer')
            ->Join('User u', 'u.UserID = a.UserID', 'left outer')
            ->Where('p.PollID', $PollID);

        $DBResult = $this->SQL->Get()->Result();
        if(!empty($DBResult)) {
            $Data = array(
                'PollID' => $DBResult[0]->PollID,
                'DiscussionID' => $DBResult[0]->DiscussionID,
                'Title' => $DBResult[0]->Text,
                'IsOpen' => $DBResult[0]->Open,
                'Questions' => array()
            );
        }

        else {
            // Pass an empty array back
            $Data = array(
                'PollID' => '',
                'DiscussionID' => '',
                'Title' => 'Opkomst',
                'IsOpen' => '',
                'Questions' => array()
            );
        }
        // Loop through the result and assemble an associative array
        foreach($DBResult as $Row) {
            if(array_key_exists($Row->QuestionID, $Data['Questions'])) {
                // Just add the option
                $exists = FALSE;
                foreach ($Data['Questions'][$Row->QuestionID]["Options"] as $Option){
                    if ($Option['OptionID'] == $Row->OptionIDs){
                        $exists = TRUE;
                    }
                }

                if(!$exists){
                    $Data['Questions'][$Row->QuestionID]['Options'][$Row->OptionIDs] = array('OptionID' => $Row->OptionIDs, 'Title' => $Row->Option, 'CountVotes' => $Row->CountVotes, 'Voters' => $Row->Name );
                } else {
                    $Data['Questions'][$Row->QuestionID]['Options'][$Row->OptionIDs]['Voters'] = $Data['Questions'][$Row->QuestionID]['Options'][$Row->OptionIDs]['Voters'] . ', ' . $Row->Name;
                }
            }
            else {
                // First time seeing this question
                // Add it and the first option
                $Data['Questions'][$Row->QuestionID] = array(
                    'QuestionID' => $Row->QuestionID,
                    'Title' => $Row->Question,
                    'Options' => array($Row->OptionIDs => array('OptionID' => $Row->OptionIDs, 'Title' => $Row->Option, 'CountVotes' => $Row->CountVotes, 'Voters' => $Row->Name)),
                    'CountResponses' => $Row->CountResponses,
                    'MultipleVote' => $Row->MultipleVote
                );
            }
        }



        // convert array to object
        $DObject = json_decode(json_encode($Data));
        //store in cache
        self::$Cache['Get'][$PollID] = $DObject;
        return $DObject;
    }

    /**
     * Convenience method to get a poll object associated with a discussion ID
     * @param int $DiscussionID
     * @return stdClass Poll object
     */
    public function GetByDiscussionID($DiscussionID) {
        $PollID = $this->GetIDByDiscussionID($DiscussionID);
        return $this->Get($PollID);
    }

    public function GetIDByDiscussionID($DiscussionID){
        //check for cached result
        $Data = GetValueR('Exists.' . $DiscussionID, self::$Cache);

        if(!empty($Data)) {
            $PollID = $Data->PollID;
        }
        else {
            $this->SQL
                ->Select('p.PollID')
                ->From('DiscussionPolls p')
                ->Where('p.DiscussionID', $DiscussionID);

            $Data = $this->SQL->Get()->FirstRow();

            if(!empty($Data)) {
                //store in cache
                self::$Cache['Exists'][$DiscussionID] = $Data;
                $PollID = $Data->PollID;
            }
            else {
                $PollID = NULL;
            }
        }
        return $PollID;
    }

    /**
     * @param $msg
     */
    public function log_er($msg){
        file_put_contents('php://stderr', print_r($msg, TRUE));
    }

    /**
     * Saves the poll object
     * @param array $FormPostValues
     */
    public function Save($FormPostValues, $update = false) {
        //paranoid

        self::PurgeCache();
        try {
            $this->Database->BeginTransaction();

            $this->SQL->Insert('DiscussionPolls', array(
                'DiscussionID' => $FormPostValues['DiscussionID'],
                'Text' => Gdn_Format::Text($FormPostValues['DP_Title'])));

            // Select the poll ID
            $this->SQL
                ->Select('p.PollID')
                ->From('DiscussionPolls p')
                ->Where('p.DiscussionID', $FormPostValues['DiscussionID']);

            $PollID = $this->SQL->Get()->FirstRow()->PollID;

            // Insert the questions
            foreach($FormPostValues['DP_Questions'] as $Index => $Question) {
                $this->SQL
                    ->Insert('DiscussionPollQuestions', array(
                            'PollID' => $PollID,
                            'Text' => Gdn_Format::Text($Question),
                            'MultipleVote' => 1-$FormPostValues['MultipleCheck'])
                    );
            }

            // Select the question IDs
            $this->SQL
                ->Select('q.QuestionID')
                ->From('DiscussionPollQuestions q')
                ->Where('q.PollID', $PollID);
            $QuestionIDs = $this->SQL->Get()->Result();

            // Insert the Options
            foreach($QuestionIDs as $Index => $QuestionID) {
                $QuestionOptions = ArrayValue('DP_Options' . $Index, $FormPostValues);
                foreach($QuestionOptions as $Option) {
                    if($Option == "") {
                        continue;
                    }
                    $this->SQL
                        ->Insert('DiscussionPollQuestionOptions', array(
                                'QuestionID' => $QuestionID->QuestionID,
                                'PollID' => $PollID,
                                'Text' => Gdn_Format::Text($Option))
                        );
                }
            }

            $this->Database->CommitTransaction();
        }
        catch(Exception $Ex) {
            $this->Database->RollbackTransaction();
            throw $Ex;
        }
    }

    /**
     * Returns whether or not a user has answered a poll
     * @param int $PollID
     * @param int $UserID
     * @return boolean
     */
    public function HasAnswered($PollID, $UserID) {
        //check for cached result
        $Data = GetValueR('Answered.' . $PollID . '_' . $UserID, self::$Cache);

        if(!empty($Data)) {
            return TRUE;
        }

        $this->SQL
            ->Select('q.PollID, a.UserID')
            ->From('DiscussionPollQuestions q')
            ->Join('DiscussionPollAnswers a', 'q.QuestionID = a.QuestionID')
            ->Where('q.PollID', $PollID)
            ->Where('a.UserID', $UserID);

        $Result = $this->SQL->Get()->Result();
        //store in cache
        self::$Cache['Answered'][$PollID . '_' . $UserID] = $Result;
        return !empty($Result);
    }

    /**
     * Returns whether or not a user has partially answered a poll
     * @param int $PollID
     * @param int $UserID
     * @return mixed false or result
     */
    public function PartialAnswer($PollID, $UserID) {
        //check for cached result
        $Data = GetValueR('Partial.' . $PollID . '_' . $UserID, self::$Cache);

        if(!empty($Data)) {
            return $Data;
        }

        $this->SQL
            ->Select('pa.*')
            ->From('DiscussionPollAnswerPartial pa')
            ->Where('pa.PollID', $PollID)
            ->Where('pa.UserID', $UserID);
        $Answered = array();
        $Answers = $this->SQL->Get()->Result();

        if(empty($Answers)) {
            return $Answered;
        }

        //create simple lookup
        foreach($Answers As $Answer) {
            $Answered[$Answer->QuestionID] = $Answer->OptionID;
        }
        //store in cache
        self::$Cache['Partial'][$PollID . '_' . $UserID] = $Answered;
        return $Answered;
    }

    /**
     * Inserts a poll vote for a specific user
     * @param array $FormPostValues
     * @param int $UserID
     * @return boolean False indicates the user has already voted
     */
    public function SaveAnswer($FormPostValues, $UserID) {
        //remove partial answers
        $this->PurgePartialAnswers($FormPostValues['PollID'], $UserID);

        //paranoid
        self::PurgeCache();

        if($this->HasAnswered($FormPostValues['PollID'], $UserID)) {
            return FALSE;
        }
        else {
            try {
                $this->Database->BeginTransaction();
                $this->_InsertAnswerData($FormPostValues, $UserID);
                $this->Database->CommitTransaction();
            }
            catch(Exception $Ex) {
                $this->Database->RollbackTransaction();
                throw $Ex;
            }
            return TRUE;
        }

        return FALSE;
    }

    protected function _InsertAnswerData($FormPostValues, $UserID) {
        foreach($FormPostValues['DP_AnswerQuestions'] as $Index => $QuestionID) {
            $MemberKey = 'DP_Answer';
            $keys = preg_grep('/'. $MemberKey .'[0-9]+/', array_keys($FormPostValues));
            $vote = array();
            $multipleVote = TRUE;
            if ($FormPostValues[$MemberKey.$Index] != false){ // No muliple voting allowed.
                $multipleVote = FALSE;
                $MemberKey .= $Index;
            } else {
                foreach($keys as $k){
                    if(!empty($FormPostValues[$k])){
                        array_push($vote, $FormPostValues[$k]);
                    }
                }
            }

            if ($multipleVote){
                foreach ($vote as $v){
                    // get Option

                    $this->SQL
                        ->Select('o.QuestionID')
                        ->From('DiscussionPollQuestionOptions o')
                        ->Where('o.OptionID', $v);

                    $DBResult = $this->SQL->Get()->Result();
                    if ($DBResult[0]->QuestionID == $QuestionID){
                        $this->SQL
                            ->Insert('DiscussionPollAnswers', array(
                                    'PollID' => $FormPostValues['PollID'],
                                    'QuestionID' => $QuestionID,
                                    'UserID' => $UserID,
                                    'OptionID' => $v)
                            );
                        $this->SQL
                            ->Update('DiscussionPollQuestionOptions')
                            ->Set('CountVotes', 'CountVotes + 1', FALSE)
                            ->Where('OptionID', $v)
                            ->Put();
                    }
                }

                $this->SQL
                    ->Update('DiscussionPollQuestions')
                    ->Set('CountResponses', 'CountResponses + 1', FALSE)
                    ->Where('QuestionID', $QuestionID)
                    ->Put();

            } else {
                $this->SQL
                    ->Insert('DiscussionPollAnswers', array(
                            'PollID' => $FormPostValues['PollID'],
                            'QuestionID' => $QuestionID,
                            'UserID' => $UserID,
                            'OptionID' => $FormPostValues[$MemberKey])
                    );
                $this->SQL
                    ->Update('DiscussionPollQuestions')
                    ->Set('CountResponses', 'CountResponses + 1', FALSE)
                    ->Where('QuestionID', $QuestionID)
                    ->Put();

                $this->SQL
                    ->Update('DiscussionPollQuestionOptions')
                    ->Set('CountVotes', 'CountVotes + 1', FALSE)
                    ->Where('OptionID', $FormPostValues[$MemberKey])
                    ->Put();
            }
        }
    }

    /**
     * Stashes Partial Answers
     * @param array $FormPostValues
     * @param int $UserID
     * @return boolean False if nothing saved
     */
    public function SavePartialAnswer($FormPostValues, $UserID) {
        $Return = FALSE;
        try {
            $this->Database->BeginTransaction();
            //remove partial answers
            $this->PurgePartialAnswers($FormPostValues['PollID'], $UserID);
            foreach($FormPostValues['DP_AnswerQuestions'] as $Index => $QuestionID) {
                $MemberKey = 'DP_Answer' . $Index;
                //ensure no null values
                if(GetValue($MemberKey, $FormPostValues)) {
                    $this->SQL
                        ->Insert('DiscussionPollAnswerPartial', array(
                                'PollID' => $FormPostValues['PollID'],
                                'QuestionID' => $QuestionID,
                                'UserID' => $UserID,
                                'OptionID' => $FormPostValues[$MemberKey])
                        );
                }
                $Return = TRUE;
            }

            $this->Database->CommitTransaction();
        }
        catch(Exception $Ex) {
            $this->Database->RollbackTransaction();
            error_log($Ex->getMessage());
        }

        return $Return;
    }


    /**
     * Delete the DiscussionEventDate
     * @param int $discussionID the id of the discussion to change
     */
    public function DeleteDiscussionEventDate($discussionID, $date){
        $this->SQL
            ->Update('Discussion')
            ->Set('DiscussionEventDate', null)
            ->Where('DiscussionID', $discussionID)
            ->Put();
    }

    /**
     * Remove Partial Answers from the database
     * @param int $PollID
     * @param int $PollID
     * @param int $UserID
     * @return boolean
     */
    public function PurgePartialAnswers($PollID, $UserID) {
        //purge cache
        self::$Cache['Partial'][$PollID . '_' . $UserID] = NULL;
        //remove from db
        return $this->SQL->Delete('DiscussionPollAnswerPartial', array('PollID' => $PollID, 'UserID' => $UserID));
    }

    /**
     * Make sure there are enough answered question for the poll submission
     * @param array $FormPostValues
     * @return boolean
     */
    public function CheckFullyAnswered($FormPostValues) {
        $Answered = array();
        foreach($FormPostValues['DP_AnswerQuestions'] as $Index => $QuestionID) {
            $MemberKey = 'DP_Answer' . $Index;
            if(GetValue($MemberKey, $FormPostValues)) {
                $Answered[$QuestionID] = $FormPostValues[$MemberKey];
            }
        }
        $Poll = $this->Get($FormPostValues['PollID']);
        if($FormPostValues['Checkboxes'] != false){
            $MemberKey = 'DP_Answer';
            $keys = preg_grep('/'. $MemberKey .'(\d+)/', array_keys($FormPostValues));
            foreach($keys as $key){
                if($FormPostValues[$key]>0){
                    $Answered[$key]=1;
                }
            }
            $this->log_er($Answered);
        }
        return count((array) $Poll->Questions) <= count($Answered);
    }

    /**
     * Removes all data associated with the poll id
     * @param int $PollID
     */
    public function Delete($PollID = Array(), $options = Array()) {
        try {
            $this->Database->BeginTransaction();
            $this->SQL->Delete('DiscussionPolls', array('PollID' => $PollID));
            $this->SQL->Delete('DiscussionPollQuestions', array('PollID' => $PollID));
            $this->SQL->Delete('DiscussionPollQuestionOptions', array('PollID' => $PollID));
            $this->SQL->Delete('DiscussionPollAnswers', array('PollID' => $PollID));
            $this->SQL->Delete('DiscussionPollAnswerPartial', array('PollID' => $PollID));
            //clear cache
            self::PurgeCache();
            $this->Database->CommitTransaction();
        }
        catch(Exception $Ex) {
            $this->Database->RollbackTransaction();
            throw $Ex;
        }
    }

    /**
     * A convenience method that removes all poll data associated with the
     * discussion id
     * @param int $DiscussionID
     */
    public function DeleteByDiscussionID($DiscussionID) {
        // make sure it exists
        if($this->Exists($DiscussionID)) {
            // no use caching as delete will wipe it out
            $this->SQL
                ->Select('p.PollID')
                ->From('DiscussionPolls p')
                ->Where('p.DiscussionID', $DiscussionID);

            $Data = $this->SQL->Get()->FirstRow();
            $PollID = $Data->PollID;

            return $this->Delete($PollID);
        }
    }

    /**
     * Closes poll associated with the discussion id
     * @param int $DiscussionID
     */
    public function Close($DiscussionID) {
        $this->SQL
            ->Update('DiscussionPolls p')
            ->Set('Open', 0)
            ->Where('p.DiscussionID', $DiscussionID)
            ->Put();
    }

    /**
     * Returns if the poll associated with a discussion id is closed or open.
     * If the poll doesn't exist, it will return true.
     * @param int $DiscussionID
     * @return boolean
     */
    public function IsClosed($DiscussionID) {
        $this->SQL
            ->Select('p.Open')
            ->From('DiscussionPolls p')
            ->Where('p.DiscussionID', $DiscussionID);
        $IsOpen = $this->SQL->Get()->FirstRow()->Open;

        return !$IsOpen;
    }

    /**
     * Wipes the cache
     */
    public static function PurgeCache() {
        // reset all the store
        foreach(self::$Cache As &$CachStore) {
            $CachStore = array();
        }
    }

}
