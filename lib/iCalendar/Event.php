<?php
/**
 * iCalendar event component class
 *
 * @link        http://tools.ietf.org/html/rfc5545
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace iCalendar;

/**
 * iCalendar event component class
 *
 */
class Event extends Base {

    /**
     * This component type.
     *
     * @var string
     */
    const TYPE = 'VEVENT';

    /**
     * Constructor
     *
     * @param string $UID
     * @return void
     */
    public function __construct($UID) {
        $this->setProperty('UID', $UID);
    }

    /**
     * Set a datetime property.
     *
     * @param string $key e.g. 'DTSTART', 'DTEND'
     * @param \DateTime $value
     * @param boolean $all Whether the datetime means all day.
     * @return void
     */
    public function setDateTime($key, \DateTime $date, $all = false) {
        if ($all) {
            // for Google Calendar
            if ($key === 'DTEND') $date->add(new \DateInterval('P1D'));
            $this->setProperty($key, $date->format('Ymd'), array('VALUE' => 'DATE'));
        } else {
            $this->setProperty($key, $date->format('Ymd\THis\Z'));
        }
    }

}
