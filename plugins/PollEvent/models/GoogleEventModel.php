<?php


class GoogleEventModel extends Gdn_Model{
    /**
     * @param bool $Offset
     * @param bool $Limit
     * @param bool $BeginDate
     * @param bool $EndDate
     * @param array $Where
     * @return mixed
     */
    public function getByDiscussionEventRange($Offset = false, $Limit = false, $BeginDate = false, $EndDate = false, $Where = array()) {

        $BeginDate = $BeginDate ? Date('Y-m-d', StrToTime($BeginDate)) : Date('Y-m-d');
        $EndDate = $EndDate ? Date('Y-m-d', StrToTime($EndDate)) : Date('Y-m-d', strtotime('+1 year'));

        $this->SQL
            ->select('d.*')
            ->from('Discussion d')
            ->where('d.DiscussionEventDate >=', $BeginDate)
            ->where('d.DiscussionEventDate <=', $EndDate)
            ->orderBy('d.DiscussionEventDate');

        $this->SQL->where($Where);
        if ($Offset !== false && $Limit !== false) {
            $this->SQL->limit($Limit, $Offset);
        }
        return $this->SQL->get();
    }

    /**
     * Convenience method to get a poll object associated with a discussion ID
     * @param int $DiscussionID
     * @return stdClass Poll object
     */
    public function GetByDiscussionID($DiscussionID) {
        return  $this->SQL
            ->Select('*')
            ->From('Discussion d')
            ->Where('d.DiscussionID', $DiscussionID)
            ->Get()
            ->FirstRow();
    }

    /**
     *Get the GoogleCalendarID by discussion
     *@param int $DiscussionID
     *@return string the corresponding GoogleCalendarID or null if not applicable
     */
    public function GetGoogleCalendarByDiscussion($DiscussionID){
        return  $this->SQL
            ->Select('d.GoogleCalendarID')
            ->From('Discussion d')
            ->Where('d.DiscussionID', $DiscussionID)
            ->Get()
            ->FirstRow()
            ->GoogleCalendarID;

    }


    /**
     * Save the DiscussionEventDate
     * @param int $discussionID the id of the discussion to change
     * @param dateTime $date The dateTime opbject representing the start time of the event
     */
    public function SaveDiscussionEventDate($discussionID, $date){
        $this->SQL
            ->Update('Discussion')
            ->Set('DiscussionEventDate', $date->format("Y-m-d H:i:s"))
            ->Where('DiscussionID', $discussionID)
            ->Put();
    }

    /**
     * Add/Update the Google calendar ID to the discussion
     * @param $DiscussionID
     * @param $eventID
     * @throws Exception
     */
    public function addGoogleCalendarByDiscussion($DiscussionID, $eventID) {
        Gdn::sql()->update('Discussion d')
                ->set('d.GoogleCalendarID', $eventID)
                ->where('d.DiscussionID', $DiscussionID)
                ->put();
    }

    /**
     * Remove the Google calendar ID from discussion
     * @param $DiscussionID
     * @throws Exception
     */
    public function removeGoogleCalendarByDiscussion($DiscussionID) {
        Gdn::sql()->update('Discussion d')
            ->set('d.GoogleCalendarID', NULL)
            ->where('d.DiscussionID', $DiscussionID)
            ->put();
    }
}
