<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database backup
    |--------------------------------------------------------------------------
    |
    | Use the absolute path on production when mysqldump is not available in
    | the PHP-FPM user's PATH. The temporary SQL file is deleted after it has
    | been sent to the browser.
    |
    */

    'mysql_dump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),

    'timeout' => (int) env('DATABASE_BACKUP_TIMEOUT', 300),
];
