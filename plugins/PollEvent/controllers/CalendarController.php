<?php

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class CalendarController extends Gdn_Controller {


    /**
     * Download ical for radix calendar
     * @throws Exception
     */
    public function ical() {
        $vCalendar = new Calendar('www.radixenschede.nl');
        $GoogleEventModel = new GoogleEventModel();
        $events = array();
        if($this->Request->get()['category']){
            foreach ($this->Request->get()['category'] as $s) {
                $CategoryModel = CategoryModel::instance();
                $cat = $CategoryModel->getWhere(array('Name' => $s))->result();
                if (count($cat) > 0) {
                    $DiscussionModel = DiscussionModel::instance();
                    $events = array_merge($events, $DiscussionModel->getWhere(array('CategoryID' => $cat[0]->CategoryID))->result());
                    $events = array_filter($events, function ($var) {
                        return !is_null($var->DiscussionEventDate);
                    });
                }
            }
        } else {
            $events = $GoogleEventModel->getByDiscussionEventRange(0, false, false, false, array());
        }

        foreach ($events as $event) {
            $startDateTime = new \DateTime($event->DiscussionEventDate);
            $startDateTime->sub(new \DateInterval('PT1H'));
            $endDateTime = new \DateTime($event->DiscussionEventDate);
            $endDateTime->add(new \DateInterval('PT2H'));
            $endDateTime->sub(new \DateInterval('PT1H'));
            $vEvent = new Event();
            $vEvent->setDtStart($startDateTime)
                ->setUniqueId('www.radixenschede.nl_' . $event->DiscussionID)
                ->setDtEnd($endDateTime)
                ->setDescription($event->Body . " \n\n " . DiscussionUrl($event))
                ->setSummary($event->Name)
                ->setLocation('Lambarene')
                ->setUrl(DiscussionUrl($event));
            $vCalendar->addComponent($vEvent);
        }
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');
        echo $vCalendar->render();
    }
}
