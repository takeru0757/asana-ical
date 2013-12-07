#!/usr/bin/php -q
<?php
/**
 * Updates ics files.
 *
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */

define('ROOT', dirname(__DIR__));
define('TMP', ROOT . DIRECTORY_SEPARATOR . 'tmp');
define('WEB', ROOT . DIRECTORY_SEPARATOR . 'web');

require ROOT . '/lib/bootstrap.php';
require ROOT . '/config.php';

$asana = new Asana\Client(
    CONFIG::ASANA_API_KEY,
    CONFIG::ASANA_WORKSPACE_ID,
    array('log' => TMP . '/logs/request.log')
);

foreach ($asana->users as $user) {
    $calendar = new iCalendar\Calendar('-//Asana-iCal//NONSGML Asana-iCal//EN');
    $calendar->setProperty('X-WR-CALNAME', "Asana ({$user->name})");
    $calendar->setProperty('X-PUBLISHED-TTL', 'PT1H');

    $tasks = $user->tasks(
        array('notes'),
        array('completed' => false, 'due_on !=' => null)
    );

    foreach ($tasks as $task) {
        if (!$task->public) continue;

        $event = new iCalendar\Event($task->id . '@asana.com');
        $event->setDateTime('DTSTART', $task->start, true);
        $event->setDateTime('DTEND', $task->end, true);
        $event->setProperty('SUMMARY', $task->summary);

        $description = $task->notes;
        if ($task->urls) {
            $description = implode("\n", $task->urls) . "\n\n" . $description;
            if (count($task->urls) === 1) {
                $event->setProperty('URL', $task->urls[0]);
            }
        }
        $event->setProperty('DESCRIPTION', $description);

        $calendar->addComponent($event);
    }

    $output = $calendar->render();
    file_put_contents(WEB . "/asana.user-{$user->id}.ics", $output);
}
