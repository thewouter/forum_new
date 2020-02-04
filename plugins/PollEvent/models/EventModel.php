<?php


class EventModel extends Gdn_Model{
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
     * Save the DiscussionEventDate
     * @param int $discussionID the id of the discussion to change
     * @param dateTime $date The dateTime opbject representing the start time of the event
     * @param $duration
     * @throws Exception
     */
    public function SaveDiscussionEventDate($discussionID, $date, $duration){
        $this->SQL
            ->Update('Discussion')
            ->Set(array('DiscussionEventDate' => $date->format("Y-m-d H:i:s"), 'DiscussionEventDuration' => $duration))
            ->Where('DiscussionID', $discussionID)
            ->Put();
    }


    /**
     * Remove the DiscussionEventDate
     * @param int $discussionID the id of the discussion to change
     * @param dateTime $date The dateTime opbject representing the start time of the event
     */
    public function RemoveDiscussionEventDate($discussionID){
        $this->SQL
            ->Update('Discussion')
            ->Set('DiscussionEventDate', null)
            ->Where('DiscussionID', $discussionID)
            ->Put();
    }
}
