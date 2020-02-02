<?php

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class CalendarController extends Gdn_Controller {
    public function test() {
        $vCalendar = new Calendar('www.radixenschede.nl');
        $GoogleEventModel = new GoogleEventModel();
        $events = $GoogleEventModel->getByDiscussionEventRange(0, false, false, false, array());
        foreach ($events as $event) {
            $vEvent = new Event();
            $vEvent->setDtStart(new \DateTime($event->DiscussionEventDate))
                ->setDtEnd((new \DateTime($event->DiscussionEventDate))->add(new DateInterval('PT2H'))->setTimezone('Europe/Amsterdam'))
                ->setSummary($event->Body);
            $vCalendar->addComponent($vEvent);
        }
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');
        echo $vCalendar->render();
    }
}
