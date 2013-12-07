# Asana-iCal

Generates [iCalendar](http://tools.ietf.org/html/rfc5545) files via [Asana](https://asana.com/).

## The Difference with Asana's Calendar Sync

Show parent tasks names and projects names as following:

```
[Project Name] Parent Task Name > The Task Name
```

Parse estimated days with putting `[n]` on beginning of the task name:

```javascript
{
    name: "[5] Preparing for Christmas",
    due_on: "2013-12-24"
}
// This task will be parsed as the event that takes from 2013-12-20 to 2013-12-24.
```

### The Same as Asana's Calendar Sync

- Includes only tasks with due dates.
- Does not include tasks marked complete.

## Installation

1. Install dependencies via [composer](http://getcomposer.org/), run `php composer.phar update`
2. Copy `config.php.sample` to `config.php`, and configure `CONFIG::ASANA_API_KEY` and `CONFIG::ASANA_WORKSPACE_ID` in this file.
  - Get Asana API key here: http://app.asana.com/-/account_api
  - Get workspaces IDs: `curl -u <api_key>: https://app.asana.com/api/1.0/workspaces`
  - See also [Asana API Documentation](http://developer.asana.com/documentation/).
3. Make `/tmp/logs` and `/web` writable, and make `bin/update.php` executable.
4. Run `php bin/update.php`

Then, ics files for each users belonging to the workspace will be generated in `/web`:

```
# "000...": User ID
asana.user-0000000000000.ics
```

### Publishing (example)

Apache:

```apache
<VirtualHost *:80>
    DocumentRoot /path/to/asana-ical/web
    ServerName cal.example.com
</VirtualHost>
```

crontab:

```
*/30 * * * * /usr/bin/php /path/to/asana-ical/bin/update.php
```

Add URL such as following to Google Calendar (ref. [Subscribe to public calendars using the calendar address](https://support.google.com/calendar/answer/37100)):

```
http://cal.example.com/asana.user-0000000000000.ics
```

## License

Copyright (c) 2013 Takeru Hirose. Released under the [MIT License](http://opensource.org/licenses/MIT).
