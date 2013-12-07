<?php
/**
 * iCalendar calendar component class
 *
 * @link        http://tools.ietf.org/html/rfc5545
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace iCalendar;

/**
 * iCalendar calendar component class
 *
 */
class Calendar extends Base {

    /**
     * This component type.
     *
     * @var string
     */
    const TYPE = 'VCALENDAR';

    /**
     * Constructor
     *
     * @param string $PRODID
     * @return void
     */
    public function __construct($PRODID) {
        $this->setProperty('VERSION', '2.0');
        $this->setProperty('PRODID', $PRODID);
    }

}
